<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
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
