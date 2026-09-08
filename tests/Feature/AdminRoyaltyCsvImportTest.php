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

    public function test_admin_can_import_by_isrc_and_duplicate_file_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'CSV Artist', 'revenue_share_percentage' => 70]);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'CSV Album', 'release_type' => 'single']);
        $song = Song::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'title' => 'CSV Song', 'isrc_code' => 'USRC17607839']);
        $store = MusicStore::where('name', 'Apple Music')->firstOrFail();
        $csv = "artist,month,music_store,isrc,track_title,units,net_revenue_eur\nCSV Artist,2026-04,Apple Music,US-RC1-76-07839,CSV Song,500,0.019978313588\nCSV Artist,2026-04,Soundcloud,US-RC1-76-07839,CSV Song,10,0.010000000000\n";
        $defaults = ['royalty_type' => 'royalties'];

        $this->actingAs($admin)->post(route('admin.royalties.import'), $defaults + [
            'csv_file' => UploadedFile::fake()->createWithContent('report.csv', $csv),
        ])->assertRedirect(route('admin.royalties'))
            ->assertSessionHas('success')
            ->assertSessionHas('warning', fn ($message) => str_contains($message, "music store 'Soundcloud' was not found"));

        $this->assertDatabaseHas('royalties', [
            'artist_id' => $artist->id, 'song_id' => $song->id, 'album_id' => $album->id,
            'store_id' => $store->id, 'month' => 4, 'year' => 2026,
            'currency' => 'EUR', 'streams' => 500,
        ]);
        $this->assertEqualsWithDelta(0.0199783136, (float) $song->royalties()->first()->amount, 0.0000000001);
        $this->assertEqualsWithDelta(0.0139848195, (float) $artist->fresh()->available_balance, 0.0000000001);

        $this->actingAs($admin)->post(route('admin.royalties.import'), $defaults + [
            'csv_file' => UploadedFile::fake()->createWithContent('report.csv', $csv),
        ])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('royalties', 1);
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
        $this->assertSame(7, Royalty::distinct('store_id')->count('store_id'));
    }
}
