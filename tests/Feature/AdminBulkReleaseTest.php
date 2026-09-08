<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminBulkReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_spotify_album_metadata(): void
    {
        config(['services.spotify_scraper.key' => 'test-key']);
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/v1/album/tracks')) {
                return Http::response(['tracks' => [[
                    'id' => 'track1',
                    'name' => 'First Song',
                    'trackNumber' => 1,
                    'durationMs' => 185000,
                    'explicit' => false,
                    'artists' => [['name' => 'Singer']],
                ]]]);
            }

            return Http::response([
                'status' => true,
                'type' => 'album',
                'id' => 'abc123def456',
                'name' => 'Fetched Album',
                'date' => '2026-08-01T00:00:00Z',
                'artists' => [['name' => 'Singer']],
                'cover' => [['url' => 'https://example.com/cover.jpg', 'width' => 640]],
                'trackCount' => 1,
            ]);
        });

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.releases.bulk-fetch'), [
            'spotify_references' => 'https://open.spotify.com/album/abc123def456',
        ])->assertOk()->assertSee('Fetched Album')->assertSee('First Song')->assertSee('2026-08-01');

        Http::assertSentCount(2);
    }

    public function test_admin_bulk_creation_needs_no_audio_and_creates_live_distributions(): void
    {
        Http::fake(['*' => Http::response('image')]);
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Singer']);
        collect([
            MusicStore::create(['name' => 'Store One', 'slug' => 'store-one', 'is_active' => true]),
            MusicStore::create(['name' => 'Store Two', 'slug' => 'store-two', 'is_active' => true]),
        ]);

        $payload = ['releases' => [[
            'artist_id' => $artist->id, 'spotify_id' => 'abc123def456', 'title' => 'Imported Album',
            'release_type' => 'single', 'release_date' => '2026-09-08', 'cover_url' => '',
            'label' => 'Label', 'upc_code' => '123', 'copyright' => '2026 Label', 'artist_names' => ['Singer'],
            'tracks' => [['title' => 'Imported Song', 'track_number' => 1, 'duration' => '3:05', 'explicit' => 0, 'artist_names' => ['Singer'], 'isrc_code' => 'USRC17607839']],
        ]]];

        $this->actingAs($admin)->post(route('admin.releases.bulk-store'), $payload)
            ->assertRedirect(route('admin.releases'));

        $this->assertDatabaseHas('albums', ['title' => 'Imported Album', 'status' => 'approved']);
        $this->assertDatabaseHas('songs', ['title' => 'Imported Song', 'audio_file' => null, 'status' => 'approved', 'isrc_code' => 'USRC17607839']);
        $this->assertDatabaseCount('distributions', MusicStore::where('is_active', true)->count());
        $this->assertDatabaseHas('distributions', ['status' => 'live']);
    }
}
