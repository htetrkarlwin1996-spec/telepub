<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
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
        $store = MusicStore::firstOrCreate(['slug' => 'csv-store'], ['name' => 'CSV Store']);
        $csv = "ISRC,Net Revenue,Quantity\nUS-RC1-76-07839,12.34,500\n";
        $defaults = ['store_id' => $store->id, 'royalty_type' => 'royalties', 'month' => 8, 'year' => 2026, 'currency' => 'USD'];

        $this->actingAs($admin)->post(route('admin.royalties.import'), $defaults + [
            'csv_file' => UploadedFile::fake()->createWithContent('report.csv', $csv),
        ])->assertRedirect(route('admin.royalties'))->assertSessionHas('success');

        $this->assertDatabaseHas('royalties', [
            'artist_id' => $artist->id, 'song_id' => $song->id, 'album_id' => $album->id,
            'store_id' => $store->id, 'amount' => 12.34, 'streams' => 500,
        ]);
        $this->assertSame(8.64, (float) $artist->fresh()->available_balance);

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
            'store_id' => $store->id, 'royalty_type' => 'royalties',
            'month' => 8, 'year' => 2026, 'currency' => 'USD',
        ])->assertSessionHasErrors('csv_file');

        $this->assertDatabaseCount('royalties', 0);
        $this->assertDatabaseCount('royalty_imports', 0);
    }
}
