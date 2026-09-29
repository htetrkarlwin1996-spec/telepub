<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShamwelaCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_fifteen_approved_releases_idempotently_without_email(): void
    {
        Http::fake(['itunes.apple.com/*' => Http::response(['results' => []])]);
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create(['email' => 'shamwela2023@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Sha Mwe LA']);
        $migration = require database_path('migrations/2026_09_21_090000_import_shamwela_approved_catalog.php');
        $migration->up();
        $migration->up();

        $albums = DB::table('albums')->where('artist_id', $artist->id);
        $this->assertSame(15, $albums->count());
        $this->assertSame(15, (clone $albums)->where('status', 'approved')->count());
        $this->assertSame(29, DB::table('songs')->where('artist_id', $artist->id)->count());
        $this->assertSame(11, DB::table('songs')->where('album_id', (clone $albums)->where('upc_code', '8447536135691')->value('id'))->count());
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_it_saves_only_an_exact_matching_published_cover(): void
    {
        Storage::fake('public');
        Http::fake([
            'itunes.apple.com/lookup*' => Http::response(['results' => [
                ['collectionName' => 'The Promise - Single', 'artistName' => 'Lian Bawi', 'artworkUrl100' => 'https://example.com/100x100bb.jpg'],
            ]]),
            'itunes.apple.com/search*' => Http::response(['results' => []]),
            'example.com/*' => Http::response('image bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $user = User::factory()->create(['email' => 'shamwela2023@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Sha Mwe LA']);
        (require database_path('migrations/2026_09_21_090000_import_shamwela_approved_catalog.php'))->up();

        $cover = DB::table('albums')->where('artist_id', $artist->id)->where('upc_code', '844735272322')->value('cover_art');
        $this->assertSame('cover_art/shamwela-1926424.jpg', $cover);
        Storage::disk('public')->assertExists($cover);
        $this->assertNull(DB::table('albums')->where('artist_id', $artist->id)->where('upc_code', '8447536072972')->value('cover_art'));
    }

    public function test_retry_command_recovers_a_previous_silent_skip(): void
    {
        Http::fake(['itunes.apple.com/*' => Http::response(['results' => []])]);
        $this->assertSame(1, Artisan::call('catalog:import-shamwela'));
        $this->assertStringContainsString('was not found', Artisan::output());

        $user = User::factory()->create(['email' => 'shamwela2023@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Sha Mwe LA']);
        $this->assertSame(0, Artisan::call('catalog:import-shamwela'));
        $this->assertSame(15, DB::table('albums')->where('artist_id', $artist->id)->count());
        $this->assertSame(0, Artisan::call('catalog:import-shamwela'));
        $this->assertSame(15, DB::table('albums')->where('artist_id', $artist->id)->count());
    }

    public function test_it_replaces_august_unassigned_total_with_apple_and_meta_track_earnings(): void
    {
        Http::fake(['itunes.apple.com/*' => Http::response(['results' => []])]);
        $user = User::factory()->create(['email' => 'shamwela2023@gmail.com']);
        $artist = Artist::create([
            'user_id' => $user->id,
            'artist_name' => 'Sha Mwe LA',
            'revenue_share_percentage' => 70,
            'total_earnings' => 151.98,
            'available_balance' => 151.98,
        ]);
        (require database_path('migrations/2026_09_21_090000_import_shamwela_approved_catalog.php'))->up();
        $storeId = DB::table('music_stores')->value('id');
        DB::table('royalties')->insert([
            'artist_id' => $artist->id,
            'store_id' => $storeId,
            'royalty_type' => 'royalties',
            'month' => 8,
            'year' => 2026,
            'amount' => 151.98 / 0.70,
            'currency' => 'USD',
            'entered_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_09_29_000000_allocate_shamwela_august_2026_royalties_to_tracks.php');
        $migration->up();
        $migration->up();

        $rows = DB::table('royalties')->where('artist_id', $artist->id)->where('year', 2026)->where('month', 8);
        $this->assertSame(50, $rows->count());
        $this->assertSame(25, (clone $rows)->distinct()->count('song_id'));
        $this->assertSame(0, (clone $rows)->whereNull('song_id')->count());

        $share = fn (string $slug) => round((float) (clone $rows)
            ->join('music_stores', 'music_stores.id', '=', 'royalties.store_id')
            ->where('music_stores.slug', $slug)
            ->sum('royalties.amount') * 0.70, 2);
        $this->assertSame(130.0, $share('apple-music'));
        $this->assertSame(23.9, $share('instagram-facebook-meta'));
        $this->assertEqualsWithDelta(153.90, (float) $artist->fresh()->total_earnings, 0.001);
        $this->assertEqualsWithDelta(153.90, (float) $artist->fresh()->available_balance, 0.001);
    }
}
