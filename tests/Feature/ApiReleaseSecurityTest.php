<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiReleaseSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_cannot_move_another_artists_song_into_their_release(): void
    {
        [$owner, $album] = $this->releaseFor('owner@example.com');
        [, $otherAlbum] = $this->releaseFor('other@example.com');
        $otherSong = Song::create([
            'album_id' => $otherAlbum->id,
            'artist_id' => $otherAlbum->artist_id,
            'title' => 'Protected song',
            'track_number' => 1,
            'status' => 'draft',
        ]);
        Sanctum::actingAs($owner);

        $this->postJson("/api/artist/releases/{$album->id}/tracks", [
            'tracks' => [[
                'id' => $otherSong->id,
                'title' => 'Stolen song',
                'track_number' => 1,
            ]],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('songs', [
            'id' => $otherSong->id,
            'album_id' => $otherAlbum->id,
            'artist_id' => $otherAlbum->artist_id,
            'title' => 'Protected song',
        ]);
    }

    public function test_unpaid_release_cannot_be_submitted_through_api(): void
    {
        [$owner, $album] = $this->releaseFor('unpaid@example.com');
        Song::create([
            'album_id' => $album->id,
            'artist_id' => $album->artist_id,
            'title' => 'Track',
            'track_number' => 1,
            'status' => 'draft',
        ]);
        $album->update(['selected_store_ids' => [1], 'payment_status' => 'unpaid']);
        Sanctum::actingAs($owner);

        $this->postJson("/api/artist/releases/{$album->id}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Payment must be completed before submitting this release.');

        $this->assertSame('draft', $album->fresh()->status);
    }

    public function test_collaborator_can_view_but_cannot_modify_owner_release(): void
    {
        [$owner, $album] = $this->releaseFor('release-owner@example.com');
        [$collaborator] = $this->releaseFor('collaborator@example.com');
        $album->collaboratingArtists()->attach($collaborator->artist->id, [
            'role' => 'collaborator',
            'share_percentage' => 25,
        ]);
        Sanctum::actingAs($collaborator);

        $this->getJson("/api/artist/releases/{$album->id}")->assertOk();
        $this->putJson("/api/artist/releases/{$album->id}", ['title' => 'Unauthorized'])
            ->assertForbidden();

        $this->assertSame('Original', $album->fresh()->title);
    }

    private function releaseFor(string $email): array
    {
        $user = User::factory()->create(['email' => $email]);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => $user->name]);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Original',
            'slug' => uniqid('release-', true),
            'status' => 'draft',
        ]);

        return [$user->fresh('artist'), $album];
    }
}
