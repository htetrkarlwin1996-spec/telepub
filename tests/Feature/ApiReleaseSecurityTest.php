<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\ReleasePayment;
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

    public function test_api_cannot_replace_cover_art_with_an_arbitrary_path(): void
    {
        [$owner, $album] = $this->releaseFor('cover-owner@example.com');
        $album->update(['cover_art' => 'cover_art/owner/safe.jpg']);
        Sanctum::actingAs($owner);

        $this->putJson("/api/artist/releases/{$album->id}", [
            'title' => 'Updated title',
            'cover_art' => 'cover_art/another-user/art.jpg',
        ])->assertOk();

        $this->assertSame('cover_art/owner/safe.jpg', $album->fresh()->cover_art);
    }

    public function test_pending_payment_blocks_web_release_deletion(): void
    {
        [$owner, $album] = $this->releaseFor('pending-payment@example.com');
        ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $owner->id,
            'provider' => 'stripe',
            'amount' => 10,
            'currency' => 'USD',
            'reference' => 'SECURITY-PENDING-1',
            'status' => 'pending',
            'addon_services' => [],
            'selected_store_ids' => [],
        ]);

        $this->actingAs($owner)->delete("/artist/catalog/{$album->id}")->assertUnprocessable();
        $this->assertDatabaseHas('albums', ['id' => $album->id]);
    }

    public function test_pending_payment_blocks_api_release_and_track_mutations(): void
    {
        [$owner, $album] = $this->releaseFor('api-payment-lock@example.com');
        $song = Song::create(['album_id' => $album->id, 'artist_id' => $album->artist_id, 'title' => 'Locked', 'track_number' => 1]);
        ReleasePayment::create([
            'album_id' => $album->id, 'user_id' => $owner->id, 'provider' => 'offline',
            'amount' => 14.99, 'currency' => 'USD', 'reference' => 'API-LOCK-1',
            'status' => 'pending', 'addon_services' => [], 'selected_store_ids' => [],
        ]);
        Sanctum::actingAs($owner);

        $this->putJson("/api/artist/releases/{$album->id}", ['title' => 'Changed'])->assertUnprocessable();
        $this->postJson("/api/artist/releases/{$album->id}/tracks", ['tracks' => [[
            'id' => $song->id, 'title' => 'Changed', 'track_number' => 1,
        ]]])->assertUnprocessable();
        $this->postJson("/api/artist/releases/{$album->id}/stores", ['store_ids' => [1]])->assertUnprocessable();

        $this->assertSame('Original', $album->fresh()->title);
        $this->assertSame('Locked', $song->fresh()->title);
    }

    public function test_master_account_can_manage_selected_artist_through_api(): void
    {
        [$artistOwner, $album] = $this->releaseFor('managed-artist@example.com');
        $manager = User::factory()->create(['role' => 'manager']);
        $master = MasterAccount::create([
            'owner_user_id' => $manager->id,
            'name' => 'API Label',
            'platform_fee_percentage' => 0,
            'default_management_fee_percentage' => 0,
            'maximum_management_fee_percentage' => 100,
        ]);
        $master->artists()->attach($artistOwner->artist->id, [
            'access_level' => 'report_only',
            'status' => 'active',
            'created_by' => $manager->id,
        ]);
        Sanctum::actingAs($manager);

        $this->withHeader('X-Artist-ID', (string) $artistOwner->artist->id)
            ->putJson("/api/artist/releases/{$album->id}", ['title' => 'Managed update'])
            ->assertOk();

        $this->assertSame('Managed update', $album->fresh()->title);
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
