<?php

namespace App\Services;

use App\Jobs\TransferReleaseAudioToSpaces;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChunkedAudioUpload
{
    public function __construct(private readonly SpacesClientFactory $spaces) {}

    public function handle(Request $request): array
    {
        if ($request->input('action') === 'single') {
            return $this->storeSingleUpload($request);
        }

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

    private function storeSingleUpload(Request $request): array
    {
        $maxKilobytes = max(1, (int) config('filesystems.release_audio_max_mb', 500)) * 1024;
        $validated = $request->validate([
            'audio_file' => ['required', 'file', 'max:'.$maxKilobytes],
        ]);
        $file = $validated['audio_file'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['mp3', 'wav', 'aac', 'flac', 'ogg'], true)) {
            throw ValidationException::withMessages(['audio_file' => 'The audio file must be MP3, WAV, AAC, FLAC, or OGG.']);
        }

        $filename = Str::uuid().'.'.$extension;
        $localPath = $file->storeAs('pending-release-audio/'.$request->user()->id, $filename, 'local');
        if ($localPath === false) {
            throw ValidationException::withMessages(['audio_file' => 'The server could not save the audio file to cPanel storage.']);
        }

        $prefix = config('filesystems.release_audio_prefix');
        $destinationPath = ($prefix ? $prefix.'/' : '').'tracks/'.$filename;

        if (config('filesystems.release_audio_disk') === 's3') {
            TransferReleaseAudioToSpaces::dispatch(
                $localPath,
                $destinationPath,
                (int) $request->user()->id,
                $file->getMimeType() ?: 'application/octet-stream',
            );
        } else {
            $stream = Storage::disk('local')->readStream($localPath);
            $stored = $stream !== false && Storage::disk(config('filesystems.release_audio_disk', 'public'))->put($destinationPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (! $stored) {
                throw ValidationException::withMessages(['audio_file' => 'The server could not save the completed audio file.']);
            }
            Storage::disk('local')->delete($localPath);
        }

        Log::channel('audio_upload')->info('Release audio staged in cPanel storage.', [
            'user_id' => $request->user()->id,
            'local_path' => $localPath,
            'destination' => $destinationPath,
            'size' => $file->getSize(),
        ]);

        return [
            'success' => true,
            'complete' => true,
            'queued' => config('filesystems.release_audio_disk') === 's3',
            'path' => $destinationPath,
            'filename' => $file->getClientOriginalName(),
        ];
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

        try {
            $isLastChunk = (int) $validated['chunk_index'] + 1 === (int) $validated['total_chunks'];
            if (! $isLastChunk) {
                return ['success' => true, 'complete' => false];
            }

            $chunks = collect($disk->files($directory))
                ->filter(fn (string $path) => preg_match('/\/\d{5}$/', $path))
                ->sort()
                ->values();
            abort_unless($chunks->count() === (int) $validated['total_chunks'], 422, 'One or more upload chunks are missing. Please retry.');

            $assembledPath = $disk->path($directory.'/assembled');
            $output = fopen($assembledPath, 'wb');
            abort_if($output === false, 422, 'Unable to create the temporary audio file.');
            foreach ($chunks as $chunk) {
                $input = fopen($disk->path($chunk), 'rb');
                abort_if($input === false, 422, 'Unable to read an uploaded chunk.');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
            fclose($output);

            $assembledBytes = filesize($assembledPath);
            abort_unless($assembledBytes === (int) $validated['total_size'], 422, 'Uploaded file size did not match. Please retry.');

            $extension = strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION));
            $prefix = config('filesystems.release_audio_prefix');
            $key = ($prefix ? $prefix.'/' : '').'tracks/'.Str::uuid().'.'.$extension;
            $stream = fopen($assembledPath, 'rb');
            abort_if($stream === false, 422, 'Unable to read the completed audio file.');

            set_time_limit(0);
            $this->spacesClient()->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $key,
                'Body' => $stream,
                'ContentLength' => $assembledBytes,
                'ContentType' => $request->file('audio_file')->getMimeType() ?: 'application/octet-stream',
            ]);
            fclose($stream);
            $disk->deleteDirectory($directory);
            Log::channel('audio_upload')->info('Staged audio upload copied from cPanel storage to Spaces.', [
                'user_id' => $request->user()->id,
                'key' => $key,
                'size' => $assembledBytes,
            ]);

            return ['success' => true, 'complete' => true, 'path' => $key, 'filename' => $validated['original_name']];
        } catch (Throwable $exception) {
            Log::channel('audio_upload')->error('Staged cPanel-to-Spaces audio upload failed.', ['user_id' => $request->user()?->id, 'chunk' => $validated['chunk_index'], 'error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
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

    private function spacesClient(): S3Client
    {
        return $this->spaces->make();
    }
}
