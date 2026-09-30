<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChunkedAudioUpload
{
    public function handle(Request $request): array
    {
        if ($request->input('action') === 'init') {
            return $this->initializeSpacesUpload($request);
        }

        if ($request->input('action') === 'complete') {
            return $this->completeSpacesUpload($request);
        }

        $maxBytes = max(1, (int) config('filesystems.release_audio_max_mb', 500)) * 1024 * 1024;
        $maxChunks = (int) ceil($maxBytes / (1024 * 1024));

        $validated = $request->validate([
            'audio_file' => ['required', 'file', 'max:1536'],
            'upload_id' => ['required', 'string', 'regex:/^[a-zA-Z0-9-]{16,80}$/'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:'.$maxChunks],
            'original_name' => ['required', 'string', 'max:255'],
            'total_size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
        ]);

        $extension = strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION));
        if (! in_array($extension, ['mp3', 'wav', 'aac', 'flac', 'ogg'], true)) {
            throw ValidationException::withMessages(['audio_file' => 'The audio file must be MP3, WAV, AAC, FLAC, or OGG.']);
        }

        $directory = 'audio-chunks/'.$request->user()->id.'/'.$validated['upload_id'];
        $chunkPath = $request->file('audio_file')->storeAs($directory, str_pad((string) $validated['chunk_index'], 5, '0', STR_PAD_LEFT), 'local');
        if ($chunkPath === false) {
            throw ValidationException::withMessages(['audio_file' => 'The server could not write the upload chunk. Check storage permissions.']);
        }

        if (config('filesystems.release_audio_disk') === 's3') {
            return $this->handleServerSideSpacesChunk($request, $validated, $directory);
        }

        if ($validated['total_chunks'] > $validated['chunk_index'] + 1) {
            return ['success' => true, 'complete' => false];
        }

        $disk = Storage::disk('local');
        $chunks = collect($disk->files($directory))->sort()->values();
        if ($chunks->count() !== $validated['total_chunks']) {
            throw ValidationException::withMessages(['audio_file' => 'One or more upload chunks are missing. Please retry.']);
        }

        $filename = Str::uuid().'.'.$extension;
        $prefix = config('filesystems.release_audio_prefix');
        $path = ($prefix ? $prefix.'/' : '').'tracks/'.$filename;
        $assembledPath = $disk->path($directory.'/assembled');

        try {
            $output = fopen($assembledPath, 'wb');
            if ($output === false) {
                throw new \RuntimeException('Unable to create the temporary audio file.');
            }

            foreach ($chunks as $chunk) {
                $input = fopen($disk->path($chunk), 'rb');
                if ($input === false) {
                    throw new \RuntimeException('Unable to read an uploaded chunk.');
                }
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
            fclose($output);

            if (filesize($assembledPath) !== $validated['total_size']) {
                throw new \RuntimeException('Uploaded file size did not match. Please retry.');
            }

            $stream = fopen($assembledPath, 'rb');
            $stored = $stream !== false && Storage::disk(config('filesystems.release_audio_disk', 'public'))->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (! $stored) {
                throw new \RuntimeException('The server could not save the completed audio file. Check the configured storage disk and credentials.');
            }
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['audio_file' => $exception->getMessage()]);
        } finally {
            $disk->deleteDirectory($directory);
        }

        return ['success' => true, 'complete' => true, 'path' => $path, 'filename' => $validated['original_name']];
    }

    private function initializeSpacesUpload(Request $request): array
    {
        $maxBytes = max(1, (int) config('filesystems.release_audio_max_mb', 500)) * 1024 * 1024;
        $validated = $request->validate([
            'original_name' => ['required', 'string', 'max:255'],
            'total_size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
            'content_type' => ['nullable', 'string', 'max:100'],
        ]);
        $extension = strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION));
        if (! in_array($extension, ['mp3', 'wav', 'aac', 'flac', 'ogg'], true)) {
            throw ValidationException::withMessages(['audio_file' => 'The audio file must be MP3, WAV, AAC, FLAC, or OGG.']);
        }

        try {
            $client = $this->spacesClient();
            $prefix = config('filesystems.release_audio_prefix');
            $key = ($prefix ? $prefix.'/' : '').'tracks/'.Str::uuid().'.'.$extension;
            $command = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'ContentType' => $validated['content_type'] ?: 'application/octet-stream',
            ]);
            $url = (string) $client->createPresignedRequest($command, '+2 hours')->getUri();
            Log::channel('audio_upload')->info('Spaces direct upload initialized.', ['user_id' => $request->user()->id, 'key' => $key, 'size' => $validated['total_size']]);

            return ['success' => true, 'mode' => 'direct', 'url' => $url, 'key' => $key];
        } catch (Throwable $exception) {
            Log::channel('audio_upload')->error('Spaces multipart initialization failed.', ['user_id' => $request->user()?->id, 'error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            throw ValidationException::withMessages(['audio_file' => 'Spaces upload could not start: '.$exception->getMessage()]);
        }
    }

    private function handleServerSideSpacesChunk(Request $request, array $validated, string $directory): array
    {
        $disk = Storage::disk('local');
        $metadataPath = $directory.'/metadata.json';

        try {
            if (! $disk->exists($metadataPath)) {
                abort_unless((int) $validated['chunk_index'] === 0, 422, 'Upload metadata is missing. Please restart the upload.');
                $extension = strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION));
                $prefix = config('filesystems.release_audio_prefix');
                $key = ($prefix ? $prefix.'/' : '').'tracks/'.Str::uuid().'.'.$extension;
                $signatureVersion = config('filesystems.disks.s3.signature_version', 'v4');
                $multipartArguments = [
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $key,
                    'ContentType' => $request->file('audio_file')->getMimeType() ?: 'application/octet-stream',
                ];

                try {
                    $result = $this->spacesClient($signatureVersion)->createMultipartUpload($multipartArguments);
                } catch (AwsException $exception) {
                    if ($signatureVersion === 'v2' || $exception->getAwsErrorCode() !== 'InvalidArgument') {
                        throw $exception;
                    }

                    $signatureVersion = 'v2';
                    Log::channel('audio_upload')->warning('Spaces rejected Signature V4 multipart initialization; retrying with Signature V2.', [
                        'user_id' => $request->user()->id,
                        'key' => $key,
                        'request_id' => $exception->getAwsRequestId(),
                    ]);
                    $result = $this->spacesClient($signatureVersion)->createMultipartUpload($multipartArguments);
                }
                $disk->put($metadataPath, json_encode([
                    'upload_id' => $result['UploadId'],
                    'key' => $key,
                    'signature_version' => $signatureVersion,
                    'parts' => [],
                    'uploaded_bytes' => 0,
                ], JSON_THROW_ON_ERROR));
                Log::channel('audio_upload')->info('Server-side Spaces multipart upload initialized.', ['user_id' => $request->user()->id, 'key' => $key, 'size' => $validated['total_size']]);
            }

            $metadata = json_decode($disk->get($metadataPath), true, flags: JSON_THROW_ON_ERROR);
            $isLastChunk = (int) $validated['chunk_index'] + 1 === (int) $validated['total_chunks'];
            $shouldUploadPart = (((int) $validated['chunk_index'] + 1) % 5 === 0) || $isLastChunk;
            if (! $shouldUploadPart) {
                return ['success' => true, 'complete' => false];
            }

            $pendingChunks = collect($disk->files($directory))
                ->filter(fn (string $path) => preg_match('/\/\d{5}$/', $path))
                ->sort()
                ->values();
            abort_if($pendingChunks->isEmpty(), 422, 'No pending audio chunks were found.');

            $partPath = $disk->path($directory.'/part-upload');
            $output = fopen($partPath, 'wb');
            foreach ($pendingChunks as $chunk) {
                $input = fopen($disk->path($chunk), 'rb');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
            fclose($output);

            $partNumber = count($metadata['parts']) + 1;
            $stream = fopen($partPath, 'rb');
            $result = $this->spacesClient($metadata['signature_version'] ?? null)->uploadPart([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $metadata['key'],
                'UploadId' => $metadata['upload_id'],
                'PartNumber' => $partNumber,
                'Body' => $stream,
            ]);
            fclose($stream);

            $partBytes = filesize($partPath);
            $metadata['parts'][] = ['PartNumber' => $partNumber, 'ETag' => $result['ETag']];
            $metadata['uploaded_bytes'] += $partBytes;
            $disk->put($metadataPath, json_encode($metadata, JSON_THROW_ON_ERROR));
            $disk->delete($pendingChunks->all());
            $disk->delete($directory.'/part-upload');

            if (! $isLastChunk) {
                return ['success' => true, 'complete' => false];
            }

            abort_unless((int) $metadata['uploaded_bytes'] === (int) $validated['total_size'], 422, 'Uploaded file size did not match. Please retry.');
            $this->spacesClient($metadata['signature_version'] ?? null)->completeMultipartUpload([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $metadata['key'],
                'UploadId' => $metadata['upload_id'],
                'MultipartUpload' => ['Parts' => $metadata['parts']],
            ]);
            $disk->deleteDirectory($directory);
            Log::channel('audio_upload')->info('Server-side Spaces multipart upload completed.', ['user_id' => $request->user()->id, 'key' => $metadata['key'], 'parts' => count($metadata['parts'])]);

            return ['success' => true, 'complete' => true, 'path' => $metadata['key'], 'filename' => $validated['original_name']];
        } catch (Throwable $exception) {
            Log::channel('audio_upload')->error('Server-side Spaces chunk upload failed.', ['user_id' => $request->user()?->id, 'chunk' => $validated['chunk_index'], 'error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            throw ValidationException::withMessages(['audio_file' => 'Spaces upload failed: '.$exception->getMessage()]);
        }
    }

    private function completeSpacesUpload(Request $request): array
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:500'],
            'original_name' => ['required', 'string', 'max:255'],
        ]);
        $prefix = config('filesystems.release_audio_prefix');
        $allowedPrefix = ($prefix ? $prefix.'/' : '').'tracks/';
        abort_unless(str_starts_with($validated['key'], $allowedPrefix), 422);

        try {
            $this->spacesClient()->headObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $validated['key'],
            ]);
            Log::channel('audio_upload')->info('Spaces direct upload confirmed.', ['user_id' => $request->user()->id, 'key' => $validated['key']]);

            return ['success' => true, 'complete' => true, 'path' => $validated['key'], 'filename' => $validated['original_name']];
        } catch (Throwable $exception) {
            Log::channel('audio_upload')->error('Spaces multipart completion failed.', ['user_id' => $request->user()?->id, 'key' => $validated['key'], 'error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            throw ValidationException::withMessages(['audio_file' => 'Spaces upload could not be completed: '.$exception->getMessage()]);
        }
    }

    private function spacesClient(?string $signatureVersion = null): S3Client
    {
        $config = config('filesystems.disks.s3');

        return new S3Client([
            'version' => 'latest',
            // DigitalOcean Spaces expects AWS Signature V4 requests to use
            // us-east-1. The actual datacenter remains selected by endpoint.
            'region' => $config['signing_region'] ?? 'us-east-1',
            'endpoint' => $config['endpoint'],
            'use_path_style_endpoint' => (bool) $config['use_path_style_endpoint'],
            'signature_version' => $signatureVersion ?? $config['signature_version'] ?? 'v4',
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'credentials' => ['key' => $config['key'], 'secret' => $config['secret']],
        ]);
    }
}
