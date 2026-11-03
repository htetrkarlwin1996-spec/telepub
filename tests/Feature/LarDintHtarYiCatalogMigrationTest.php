<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LarDintHtarYiCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_imports_the_approved_catalog_into_the_requested_account(): void
    {
        $user = User::where('email', 'lardint00@gmail.com')->firstOrFail();

        $this->assertSame('artist', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);

        $artist = $user->artist()->firstOrFail();
        $this->assertSame('Lar Dint Htar Yi', $artist->artist_name);
        $this->assertEquals(85, $artist->revenue_share_percentage);

        $albums = Album::where('artist_id', $artist->id)->get();
        $this->assertCount(15, $albums);
        $this->assertSame(15, $albums->where('status', 'approved')->count());
        $this->assertSame(15, $albums->where('payment_status', 'paid')->count());
        $this->assertSame(4, $albums->where('release_type', 'album')->count());
        $this->assertSame(11, $albums->where('release_type', 'single')->count());
        $this->assertSame(15, $artist->songs()->where('status', 'approved')->count());

        $release = Album::where('artist_id', $artist->id)
            ->where('upc_code', '8447536472345')
            ->firstOrFail();
        $song = $release->songs()->where('isrc_code', 'NLRD52349869')->firstOrFail();
        $this->assertSame('Ma Pyaung Lel Khae Par', $release->title);
        $this->assertSame(['Saw Eh'], $song->featuring);
        $this->assertSame(['Lar Dint Htar Yi', 'Saw Eh'], $song->vocals);

        $producerRelease = Album::where('artist_id', $artist->id)
            ->where('upc_code', '8447536472437')
            ->firstOrFail();
        $this->assertSame(['Myint Moe Aung'], $producerRelease->songs()->firstOrFail()->producers);

        foreach ($albums as $album) {
            Storage::disk('public')->assertExists($album->cover_art);
        }
    }

    public function test_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_11_03_000000_import_lar_dint_htar_yi_approved_catalog.php');
        $migration->up();

        $user = User::where('email', 'lardint00@gmail.com')->firstOrFail();
        $artist = $user->artist()->firstOrFail();

        $this->assertSame(1, User::where('email', 'lardint00@gmail.com')->count());
        $this->assertSame(15, Album::where('artist_id', $artist->id)->count());
        $this->assertSame(15, $artist->songs()->count());
    }
}
