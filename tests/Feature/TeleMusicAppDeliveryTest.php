<?php

namespace Tests\Feature;

use App\Jobs\DeliverReleaseToTeleMusicApp;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Distribution;
use App\Models\MusicStore;
use App\Models\Song;
use App\Models\User;
use App\Services\TeleMusicAppDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeleMusicAppDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_selected_release_is_queued_for_telemusic_app(): void
    {
        Queue::fake();
        [$album] = $this->releaseWithTeleMusicDistribution();

        $this->assertTrue(app(TeleMusicAppDelivery::class)->dispatchIfSelected($album));
        Queue::assertPushed(DeliverReleaseToTeleMusicApp::class, fn ($job) => $job->albumId === $album->id);
    }

    public function test_job_sends_signed_idempotent_release_payload_and_marks_delivery_complete(): void
    {
        Storage::fake('public');
        config([
            'filesystems.release_audio_disk' => 'public',
            'services.telemusic_app.api_url' => 'https://app-backend.test',
            'services.telemusic_app.api_token' => 'private-token',
            'services.telemusic_app.webhook_secret' => 'signing-secret',
        ]);
        Http::fake([
            'https://app-backend.test/api/v1/distribution/releases' => Http::response([
                'success' => true,
                'release_id' => 'app-release-99',
                'release_url' => 'https://app.test/releases/app-release-99',
            ]),
        ]);
        [$album, $distribution] = $this->releaseWithTeleMusicDistribution();

        (new DeliverReleaseToTeleMusicApp($album->id))->handle();

        Http::assertSent(function ($request) use ($album) {
            $timestamp = $request->header('X-TeleMusic-Timestamp')[0] ?? '';
            $signature = $request->header('X-TeleMusic-Signature')[0] ?? '';

            return $request->url() === 'https://app-backend.test/api/v1/distribution/releases'
                && $request->hasHeader('Authorization', 'Bearer private-token')
                && $request->hasHeader('Idempotency-Key', 'telemusic-release-'.$album->id)
                && hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->body(), 'signing-secret'), $signature)
                && $request['external_release_id'] === $album->id
                && $request['tracks'][0]['title'] === 'App Track';
        });

        $distribution->refresh();
        $this->assertSame('delivered', $distribution->delivery_status);
        $this->assertSame('live', $distribution->status);
        $this->assertSame('app-release-99', $distribution->external_release_id);
        $this->assertSame('https://app.test/releases/app-release-99', $distribution->store_url);
        $this->assertNotNull($distribution->delivered_at);
    }

    private function releaseWithTeleMusicDistribution(): array
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'App Artist']);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'App Release',
            'release_type' => 'single',
            'status' => 'approved',
            'approved_at' => now(),
            'cover_art' => 'cover_art/app-release.jpg',
        ]);
        $song = Song::create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'App Track',
            'track_number' => 1,
            'audio_file' => 'tracks/app-track.flac',
            'status' => 'approved',
        ]);
        $store = MusicStore::where('slug', 'telemusic-app')->firstOrFail();
        $distribution = Distribution::create([
            'album_id' => $album->id,
            'song_id' => $song->id,
            'artist_id' => $artist->id,
            'store_id' => $store->id,
            'status' => 'approved',
            'delivery_status' => 'pending',
        ]);

        return [$album, $distribution];
    }
}
