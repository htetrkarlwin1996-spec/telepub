<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChunkedAudioUpload
{
    public function handle(Request $request): array
    {
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
}
