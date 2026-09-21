<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IreneApprovedCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_catalog_is_imported_as_approved_without_distributions(): void
    {
        $user = User::factory()->create(['email' => 'irenezinmarmyint20224@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Irene Zin Mar Myint']);

        $marMigration = require database_path('migrations/2026_09_21_050000_add_mar_deluxe_to_irene_account.php');
        $catalogMigration = require database_path('migrations/2026_09_21_060000_import_irene_approved_catalog.php');

        $marMigration->up();
        $catalogMigration->up();
        $catalogMigration->up();

        $albumIds = DB::table('albums')->where('artist_id', $artist->id)->pluck('id');

        $this->assertCount(38, $albumIds);
        $this->assertSame(38, DB::table('albums')->whereIn('id', $albumIds)->where('status', 'approved')->count());
        $this->assertSame(84, DB::table('songs')->whereIn('album_id', $albumIds)->count());
        $this->assertSame(84, DB::table('songs')->whereIn('album_id', $albumIds)->where('status', 'approved')->count());
        $this->assertSame(0, DB::table('distributions')->whereIn('album_id', $albumIds)->count());

        $chitLohId = DB::table('albums')->where('artist_id', $artist->id)->where('upc_code', '8447536615889')->value('id');
        $this->assertSame(13, DB::table('songs')->where('album_id', $chitLohId)->count());
        $this->assertSame('QZXLZ2550943', DB::table('songs')->where('album_id', $chitLohId)->orderBy('track_number')->value('isrc_code'));
        $this->assertSame('QZXLZ2550955', DB::table('songs')->where('album_id', $chitLohId)->orderByDesc('track_number')->value('isrc_code'));
    }
}
