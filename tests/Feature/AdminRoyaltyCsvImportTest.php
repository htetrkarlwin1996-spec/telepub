<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminRoyaltyCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_multiple_store_royalties_manually_at_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Bulk Artist', 'revenue_share_percentage' => 70]);
        $stores = MusicStore::take(3)->get();

        $this->actingAs($admin)->post(route('admin.royalties.bulk-manual'), [
            'artist_id' => $artist->id,
            'royalty_type' => 'royalties',
            'month' => 4,
            'year' => 2026,
            'currency' => 'EUR',
            'stores' => [
                ['store_id' => $stores[0]->id, 'amount' => '10.25', 'streams' => 100],
                ['store_id' => $stores[1]->id, 'amount' => '20.75', 'streams' => 200],
                ['store_id' => $stores[2]->id, 'amount' => '', 'streams' => ''],
            ],
        ])->assertRedirect(route('admin.royalties'))->assertSessionHas('success');

        $this->assertDatabaseCount('royalties', 2);
        $this->assertSame(['USD'], Royalty::distinct()->pluck('currency')->all());
        $this->assertEqualsWithDelta(21.7, (float) $artist->fresh()->available_balance, 0.0000001);
    }

    public function test_database_store_logos_are_visible_to_admin_and_artist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Logo Artist']);
        $store = MusicStore::create([
            'name' => 'Logo Store',
            'slug' => 'logo-store',
            'logo' => 'https://example.com/store-logo.png',
        ]);
        Royalty::create([
            'artist_id' => $artist->id,
            'store_id' => $store->id,
            'royalty_type' => 'royalties',
            'month' => 4,
            'year' => 2026,
            'amount' => 1,
            'currency' => 'USD',
            'entered_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('admin.royalties'))
            ->assertOk()->assertSee('https://example.com/store-logo.png');
        $this->actingAs($artistUser)->get(route('artist.royalties'))
            ->assertOk()->assertSee('https://example.com/store-logo.png');
    }

    public function test_royalty_page_renders_with_new_and_unknown_store_logos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        MusicStore::create(['name' => 'Unknown DSP', 'slug' => 'unknown-dsp']);

        $this->actingAs($admin)
            ->get(route('admin.royalties'))
            ->assertOk()
            ->assertSee('YouTube Audio Content ID')
            ->assertSee('YouTube Art Tracks')
            ->assertSee('Unknown DSP');
    }

    public function test_admin_can_import_by_isrc_and_duplicate_file_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'CSV Artist', 'revenue_share_percentage' => 70]);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'CSV Album', 'release_type' => 'single']);
        $song = Song::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'title' => 'CSV Song', 'isrc_code' => 'USRC17607839']);
        $store = MusicStore::where('name', 'Apple Music')->firstOrFail();
        $csv = "artist,month,music_store,isrc,track_title,units,net_revenue_eur\nCSV Artist,2026-04,Apple Music,US-RC1-76-07839,CSV Song,500,0.019978313588\nCSV Artist,2026-04,Amazon Prime,US-RC1-76-07839,CSV Song,2,-0.010000000000\nCSV Artist,2026-04,Soundcloud,US-RC1-76-07839,CSV Song,10,0.010000000000\n";
        $defaults = ['royalty_type' => 'royalties'];

        $this->actingAs($admin)->post(route('admin.royalties.import'), $defaults + [
            'csv_file' => UploadedFile::fake()->createWithContent('report.csv', $csv),
        ])->assertRedirect(route('admin.royalties'))
            ->assertSessionHas('success')
            ->assertSessionHas('warning', fn ($message) => str_contains($message, "music store 'Soundcloud' was not found"));

        $this->assertDatabaseHas('royalties', [
            'artist_id' => $artist->id, 'song_id' => $song->id, 'album_id' => $album->id,
            'store_id' => $store->id, 'month' => 4, 'year' => 2026,
            'currency' => 'USD', 'streams' => 500,
        ]);
        $this->assertEqualsWithDelta(0.0199783136, (float) $song->royalties()->first()->amount, 0.0000000001);
        $this->assertEqualsWithDelta(0.0069848195, (float) $artist->fresh()->available_balance, 0.0000000001);

        $this->actingAs($admin)->post(route('admin.royalties.import'), $defaults + [
            'csv_file' => UploadedFile::fake()->createWithContent('report.csv', $csv),
        ])->assertSessionHas('info', fn ($message) => str_contains($message, '2 rows were already imported'));
        $this->assertDatabaseCount('royalties', 2);
    }

    public function test_import_rolls_back_when_an_isrc_does_not_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = MusicStore::first();

        $this->actingAs($admin)->post(route('admin.royalties.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('bad.csv', "ISRC,Amount\nUNKNOWN123456,10.00\n"),
            'royalty_type' => 'royalties',
        ])->assertSessionHasErrors('csv_file');

        $this->assertDatabaseCount('royalties', 0);
        $this->assertDatabaseCount('royalty_imports', 0);
    }

    public function test_report_channel_names_map_to_existing_music_stores(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Bu Thee']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Album', 'release_type' => 'album']);
        Song::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'title' => 'Track', 'isrc_code' => 'QZYFZ2463792']);
        $channels = ['Apple Music', 'META', 'NetEase Cloud Music', 'Spotify', 'Tencent', 'TikTok', 'YouTube Art Tracks', 'YouTube Audio Content ID'];
        $csv = "artist,month,music_store,isrc,track_title,units,net_revenue_eur\n";
        foreach ($channels as $channel) {
            $csv .= "Bu Thee,2026-04,{$channel},QZYFZ2463792,Track,1,0.010000000000\n";
        }

        $this->actingAs($admin)->post(route('admin.royalties.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('apr-2026.csv', $csv),
        ])->assertSessionHas('success');

        $this->assertDatabaseCount('royalties', count($channels));
        $this->assertSame(8, Royalty::distinct('store_id')->count('store_id'));

        $youtubeMusic = MusicStore::where('slug', 'youtube-music')->firstOrFail();
        Royalty::whereIn('import_row', [8, 9])->update(['store_id' => $youtubeMusic->id]);

        $this->actingAs($admin)->post(route('admin.royalties.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('apr-2026.csv', $csv),
        ])->assertSessionHas('info');

        $this->assertDatabaseHas('royalties', [
            'import_row' => 8,
            'store_id' => MusicStore::where('slug', 'youtube-art-tracks')->value('id'),
        ]);
        $this->assertDatabaseHas('royalties', [
            'import_row' => 9,
            'store_id' => MusicStore::where('slug', 'youtube-audio-content-id')->value('id'),
        ]);
    }
}
