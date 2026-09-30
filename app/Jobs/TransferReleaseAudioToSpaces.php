<?php

namespace App\Jobs;

use Aws\S3\S3Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TransferReleaseAudioToSpaces implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1200;

    public array $backoff = [60, 300];

    public function __construct(
        public string $localPath,
        public string $destinationPath,
        public int $userId,
        public string $contentType = 'application/octet-stream',
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk('local');
        $absolutePath = $disk->path($this->localPath);
        $stream = fopen($absolutePath, 'rb');

        if ($stream === false) {
            throw new \RuntimeException('The staged audio file is missing or unreadable.');
        }

        $config = config('filesystems.disks.s3');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'endpoint' => $config['endpoint'],
            'use_path_style_endpoint' => (bool) $config['use_path_style_endpoint'],
            'signature_version' => 'v4',
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'credentials' => ['key' => $config['key'], 'secret' => $config['secret']],
        ]);

        try {
            $client->putObject([
                'Bucket' => $config['bucket'],
                'Key' => $this->destinationPath,
                'Body' => $stream,
                'ContentLength' => filesize($absolutePath),
                'ContentType' => $this->contentType,
            ]);
            $disk->delete($this->localPath);
            Log::channel('audio_upload')->info('Queued release audio transferred from cPanel to Spaces.', [
                'user_id' => $this->userId,
                'key' => $this->destinationPath,
            ]);
        } finally {
            fclose($stream);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::channel('audio_upload')->error('Queued cPanel-to-Spaces audio transfer failed permanently.', [
            'user_id' => $this->userId,
            'local_path' => $this->localPath,
            'key' => $this->destinationPath,
            'error' => $exception?->getMessage(),
        ]);
    }
}
