<?php

namespace App\Jobs;

use App\Models\Album;
use App\Services\AdminNotifier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeliverReleaseToTeleMusicApp implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 180;

    public array $backoff = [60, 300, 900, 3600];

    public int $uniqueFor = 7200;

    public function __construct(public int $albumId) {}

    public function uniqueId(): string
    {
        return 'telemusic-app-album-'.$this->albumId;
    }

    public function handle(): void
    {
        $album = Album::with(['artist', 'songs', 'collaboratingArtists'])->findOrFail($this->albumId);
        $distributions = $album->distributions()
            ->whereHas('store', fn ($query) => $query->where('delivery_driver', 'telemusic_api'))
            ->get();

        if ($album->status !== 'approved' || $distributions->isEmpty() || $distributions->every(fn ($distribution) => $distribution->delivery_status === 'delivered')) {
            return;
        }

        $apiUrl = rtrim((string) config('services.telemusic_app.api_url'), '/');
        $token = (string) config('services.telemusic_app.api_token');
        $secret = (string) config('services.telemusic_app.webhook_secret');
        if ($apiUrl === '' || $token === '' || $secret === '') {
            throw new RuntimeException('TeleMusic App API configuration is incomplete.');
        }

        $distributions->each->update([
            'delivery_status' => 'delivering',
            'delivery_attempts' => $distributions->max('delivery_attempts') + 1,
            'last_delivery_attempt_at' => now(),
            'delivery_error' => null,
        ]);

        $payload = $this->payload($album);
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->contentType('application/json')
                ->withHeaders([
                    'Idempotency-Key' => 'telemusic-release-'.$album->id,
                    'X-TeleMusic-Timestamp' => $timestamp,
                    'X-TeleMusic-Signature' => hash_hmac('sha256', $timestamp.'.'.$body, $secret),
                ])
                ->timeout(120)
                ->withBody($body, 'application/json')
                ->post($apiUrl.'/api/v1/distribution/releases')
                ->throw();

            $result = $response->json();
            if (! data_get($result, 'success')) {
                throw new RuntimeException('TeleMusic App returned an unsuccessful response.');
            }

            $distributions->each->update([
                'delivery_status' => 'delivered',
                'external_release_id' => (string) data_get($result, 'release_id'),
                'store_url' => data_get($result, 'release_url'),
                'delivery_response' => $result,
                'delivery_error' => null,
                'delivered_at' => now(),
                'status' => 'live',
                'live_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $distributions->each->update([
                'delivery_status' => 'failed',
                'delivery_error' => mb_strimwidth($exception->getMessage(), 0, 2000, '…'),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $album = Album::with('artist')->find($this->albumId);
        if (! $album) {
            return;
        }

        $album->distributions()->whereHas('store', fn ($query) => $query->where('delivery_driver', 'telemusic_api'))->update([
            'delivery_status' => 'failed',
            'delivery_error' => mb_strimwidth($exception?->getMessage() ?: 'Delivery failed.', 0, 2000, '…'),
        ]);

        app(AdminNotifier::class)->activity(
            'telemusic_app_delivery_failed',
            'TeleMusic App delivery failed',
            'Release “'.$album->title.'” could not be delivered after all retry attempts.',
            route('admin.releases.show', $album),
            ['Release ID' => $album->id, 'Artist' => $album->artist?->artist_name ?? 'Unknown', 'Error' => $exception?->getMessage() ?: 'Unknown error']
        );
    }

    private function payload(Album $album): array
    {
        return [
            'external_release_id' => $album->id,
            'title' => $album->title,
            'release_type' => $album->release_type,
            'release_date' => $album->release_date?->toDateString(),
            'genre' => $album->genre,
            'label' => $album->label,
            'copyright_holder' => $album->copyright_holder,
            'phonogram_right_holder' => $album->phonogram_right_holder,
            'upc' => $album->upc_code,
            'cover_url' => $this->fileUrl($album->cover_art, 'public'),
            'artist' => ['external_artist_id' => $album->artist_id, 'name' => $album->artist?->artist_name],
            'collaborators' => $album->collaboratingArtists->map(fn ($artist) => [
                'external_artist_id' => $artist->id,
                'name' => $artist->artist_name,
                'share_percentage' => (float) $artist->pivot->share_percentage,
            ])->values()->all(),
            'tracks' => $album->songs->map(fn ($song) => [
                'external_track_id' => $song->id,
                'title' => $song->title,
                'version' => $song->version,
                'track_number' => $song->track_number,
                'duration' => $song->duration,
                'isrc' => $song->isrc_code,
                'audio_url' => $this->fileUrl($song->audio_file ?: $song->file_path, config('filesystems.release_audio_disk')),
                'explicit' => (bool) $song->explicit,
                'genre' => $song->genre,
                'language' => $song->language,
                'primary_artists' => $song->primary_artists ?? [],
                'composers' => $song->composers ?? [],
                'lyricist' => $song->lyricist ?? [],
                'producers' => $song->producers ?? [],
                'vocals' => $song->vocals ?? [],
                'featuring' => $song->featuring ?? [],
                'lyrics' => $song->lyrics,
            ])->values()->all(),
        ];
    }

    private function fileUrl(?string $path, string $disk): ?string
    {
        if (blank($path)) {
            return null;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk($disk)->url($path);
    }
}
