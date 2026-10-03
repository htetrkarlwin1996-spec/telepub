<?php

namespace App\Jobs;

use App\Services\SpacesClientFactory;
use Aws\Exception\AwsException;
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

    public function handle(SpacesClientFactory $spaces): void
    {
        $disk = Storage::disk('local');
        $absolutePath = $disk->path($this->localPath);
        $stream = fopen($absolutePath, 'rb');

        if ($stream === false) {
            throw new \RuntimeException('The staged audio file is missing or unreadable.');
        }

        $config = config('filesystems.disks.s3');

        try {
            $spaces->make()->putObject([
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
        } catch (AwsException $exception) {
            Log::channel('audio_upload')->error('Spaces rejected the queued audio transfer.', [
                'user_id' => $this->userId,
                'key' => $this->destinationPath,
                'aws_error_code' => $exception->getAwsErrorCode(),
                'aws_error_message' => $exception->getAwsErrorMessage(),
                'aws_request_id' => $exception->getAwsRequestId(),
                'status_code' => $exception->getStatusCode(),
            ]);

            throw $exception;
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
