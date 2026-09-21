<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarDeluxeCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mar_deluxe_is_added_to_the_requested_artist_account_once(): void
    {
        $user = User::factory()->create(['email' => 'irenezinmarmyint20224@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Irene Zin Mar Myint']);

        $migration = require database_path('migrations/2026_09_21_050000_add_mar_deluxe_to_irene_account.php');
        $migration->up();
        $migration->up();

        $album = DB::table('albums')->where('artist_id', $artist->id)->where('upc_code', '8447536112975')->first();

        $this->assertNotNull($album);
        $this->assertSame('MAR (Deluxe)', $album->title);
        $this->assertSame('draft', $album->status);
        $this->assertSame(13, DB::table('songs')->where('album_id', $album->id)->count());
        $this->assertSame('QZXLZ2526056', DB::table('songs')->where('album_id', $album->id)->orderBy('track_number')->value('isrc_code'));
        $this->assertSame(0, DB::table('distributions')->where('album_id', $album->id)->count());
    }
}
