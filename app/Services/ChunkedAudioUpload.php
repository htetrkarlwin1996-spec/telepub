<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChunkedAudioUpload
{
    public function handle(Request $request): array
    {
        $validated = $request->validate([
            'audio_file' => ['required', 'file', 'max:6144'],
            'upload_id' => ['required', 'string', 'regex:/^[a-zA-Z0-9-]{16,80}$/'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:20'],
            'original_name' => ['required', 'string', 'max:255'],
            'total_size' => ['required', 'integer', 'min:1', 'max:52428800'],
        ]);

        $extension = strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION));
        if (! in_array($extension, ['mp3', 'wav', 'aac', 'flac', 'ogg'], true)) {
            throw ValidationException::withMessages(['audio_file' => 'The audio file must be MP3, WAV, AAC, FLAC, or OGG.']);
        }

        $directory = 'audio-chunks/'.$request->user()->id.'/'.$validated['upload_id'];
        $chunkPath = $request->file('audio_file')->storeAs($directory, str_pad((string) $validated['chunk_index'], 5, '0', STR_PAD_LEFT), 'local');

        if ($validated['total_chunks'] > $validated['chunk_index'] + 1) {
            return ['success' => true, 'complete' => false];
        }

        $disk = Storage::disk('local');
        $chunks = collect($disk->files($directory))->sort()->values();
        if ($chunks->count() !== $validated['total_chunks']) {
            throw ValidationException::withMessages(['audio_file' => 'One or more upload chunks are missing. Please retry.']);
        }

        $filename = Str::uuid().'.'.$extension;
        $destination = Storage::disk('public')->path('tracks/'.$filename);
        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }
        $output = fopen($destination, 'wb');
        foreach ($chunks as $chunk) {
            $input = fopen($disk->path($chunk), 'rb');
            stream_copy_to_stream($input, $output);
            fclose($input);
        }
        fclose($output);
        $disk->deleteDirectory($directory);

        if (filesize($destination) !== $validated['total_size']) {
            Storage::disk('public')->delete('tracks/'.$filename);
            throw ValidationException::withMessages(['audio_file' => 'Uploaded file size did not match. Please retry.']);
        }

        return ['success' => true, 'complete' => true, 'path' => 'tracks/'.$filename, 'filename' => $validated['original_name']];
    }
}
