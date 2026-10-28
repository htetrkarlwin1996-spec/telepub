<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\ReleasePayment;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAudioDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_a_release_audio_file(): void
    {
        Storage::fake('public');
        config()->set('filesystems.release_audio_disk', 'public');

        [$admin, $artistUser, $album, $song] = $this->releaseWithAudio();
        Storage::disk('public')->put($song->audio_file, 'lossless-audio-content');

        $this->actingAs($admin)
            ->get(route('admin.releases.show', $album))
            ->assertOk()
            ->assertSee('Download Audio')
            ->assertSee(route('admin.songs.download-audio', $song), false);

        $response = $this->get(route('admin.songs.download-audio', $song));

        $response->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=download-artist-master-track.flac');
        $this->assertSame('lossless-audio-content', $response->streamedContent());

        $this->actingAs($artistUser)
            ->get(route('admin.songs.download-audio', $song))
            ->assertForbidden();
    }

    public function test_audio_download_returns_not_found_when_the_stored_file_is_missing(): void
    {
        Storage::fake('public');
        config()->set('filesystems.release_audio_disk', 'public');

        [$admin, , , $song] = $this->releaseWithAudio();

        $this->actingAs($admin)
            ->get(route('admin.songs.download-audio', $song))
            ->assertNotFound();
    }

    public function test_admin_can_see_release_payment_amount_and_selected_addons(): void
    {
        [$admin, $artistUser, $album] = $this->releaseWithAudio();
        $album->update([
            'payment_status' => 'paid',
            'selected_addons' => ['composer_songwriter', 'mechanical'],
        ]);
        ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $artistUser->id,
            'provider' => 'myanmyanpay',
            'status' => 'paid',
            'amount' => 8955,
            'currency' => 'MMK',
            'reference' => 'REL-MYA-PAYMENT-1',
            'addon_services' => ['composer_songwriter', 'mechanical'],
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.releases.show', $album))
            ->assertOk()
            ->assertSeeText('MMK 8,955.00')
            ->assertSeeText('MyanMyanPay MMQR')
            ->assertSeeText('Composer / Songwriter Royalties')
            ->assertSeeText('Mechanical Royalties')
            ->assertSeeText('REL-MYA-PAYMENT-1');
    }

    private function releaseWithAudio(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $artistUser = User::factory()->create(['role' => 'artist', 'is_active' => true]);
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Download Artist',
        ]);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Download Album',
            'release_type' => 'single',
            'genre' => 'Pop',
            'status' => 'approved',
        ]);
        $song = Song::create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Master Track',
            'track_number' => 1,
            'audio_file' => 'tracks/master-track.flac',
            'status' => 'approved',
        ]);

        return [$admin, $artistUser, $album, $song];
    }
}
