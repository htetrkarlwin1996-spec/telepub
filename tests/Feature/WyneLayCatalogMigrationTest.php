<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WyneLayCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_imports_four_approved_singles_with_cover_art(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email' => 'wintyeeshunn2@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Wyne Lay']);

        $migration = require database_path('migrations/2026_10_05_010000_import_wyne_lay_approved_singles.php');
        $migration->up();

        $this->assertSame(4, $artist->albums()->where('status', 'approved')->count());
        $this->assertSame(4, $artist->songs()->where('status', 'approved')->count());
        $this->assertDatabaseHas('albums', [
            'artist_id' => $artist->id,
            'upc_code' => '8447721168626',
            'title' => 'Yay Tway Pat (New Version)',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('songs', [
            'artist_id' => $artist->id,
            'isrc_code' => 'QZXLZ2518966',
            'version' => 'TBoy Remix',
        ]);

        foreach (['yay-tway-pat.jpg', 'htar-khae-mar-lar-tboy-remix.jpg', 'snow-city.jpg', 'yay-tway-pat-new-version.jpg'] as $cover) {
            Storage::disk('public')->assertExists('cover_art/wyne-lay/'.$cover);
        }

        // Re-running must update the same records without creating duplicates.
        $migration->up();
        $this->assertSame(4, $artist->albums()->count());
        $this->assertSame(4, $artist->songs()->count());
    }
}
