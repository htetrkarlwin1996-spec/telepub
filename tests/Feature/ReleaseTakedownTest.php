<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Distribution;
use App\Models\MusicStore;
use App\Models\ReleaseTakedownRequest;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReleaseTakedownTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_request_take_down_once_for_an_approved_release(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Take Down Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Live Release', 'release_type' => 'single', 'status' => 'approved', 'approved_at' => now()]);

        $this->actingAs($user)->post(route('artist.catalog.take-down', $album), ['reason' => 'Rights have expired.'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('release_takedown_requests', ['album_id' => $album->id, 'status' => 'pending', 'reason' => 'Rights have expired.']);

        $this->post(route('artist.catalog.take-down', $album), ['reason' => 'Duplicate'])
            ->assertSessionHasErrors('reason');
        $this->assertSame(1, ReleaseTakedownRequest::where('album_id', $album->id)->count());
    }

    public function test_admin_approval_marks_release_and_distributions_as_taken_down(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $artist = Artist::create(['artist_name' => 'Take Down Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Live Release', 'release_type' => 'single', 'status' => 'approved', 'approved_at' => now()]);
        $song = Song::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'title' => 'Track', 'track_number' => 1]);
        $store = MusicStore::create(['name' => 'Store', 'slug' => 'store']);
        $distribution = Distribution::create(['song_id' => $song->id, 'album_id' => $album->id, 'artist_id' => $artist->id, 'store_id' => $store->id, 'status' => 'live']);
        $request = ReleaseTakedownRequest::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'reason' => 'Requested', 'status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.release-takedowns.approve', $request), ['admin_notes' => 'Approved'])
            ->assertSessionHas('success');

        $this->assertSame('approved', $request->fresh()->status);
        $this->assertNotNull($album->fresh()->taken_down_at);
        $this->assertNotNull($distribution->fresh()->taken_down_at);
    }
}
