<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TayzarRoyaltyPeriodMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_moves_only_tayzar_royalties_four_months_back_once(): void
    {
        $tayzarUser = User::factory()->create(['email' => 'tayzarst91@gmail.com']);
        $tayzar = Artist::create(['user_id' => $tayzarUser->id, 'artist_name' => 'Tayzar']);
        $otherUser = User::factory()->create();
        $other = Artist::create(['user_id' => $otherUser->id, 'artist_name' => 'Other Artist']);
        $store = MusicStore::create(['name' => 'Test Store', 'slug' => 'test-store']);

        Royalty::create(['artist_id' => $tayzar->id, 'store_id' => $store->id, 'year' => 2026, 'month' => 9, 'amount' => 24.78, 'streams' => 100, 'entered_by' => $tayzarUser->id]);
        Royalty::create(['artist_id' => $tayzar->id, 'store_id' => $store->id, 'year' => 2025, 'month' => 5, 'amount' => 2.56, 'streams' => 200, 'entered_by' => $tayzarUser->id]);
        Royalty::create(['artist_id' => $other->id, 'store_id' => $store->id, 'year' => 2026, 'month' => 9, 'amount' => 10, 'streams' => 300, 'entered_by' => $otherUser->id]);

        $migration = require database_path('migrations/2026_09_30_020000_shift_tayzar_royalty_periods_four_months.php');
        $migration->up();
        $migration->up();

        $this->assertSame(
            [[2025, 1], [2026, 5]],
            Royalty::where('artist_id', $tayzar->id)->orderBy('year')->orderBy('month')->get()->map(fn ($royalty) => [$royalty->year, $royalty->month])->all(),
        );
        $this->assertEquals(27.34, Royalty::where('artist_id', $tayzar->id)->sum('amount'));
        $this->assertSame(300, (int) Royalty::where('artist_id', $tayzar->id)->sum('streams'));
        $this->assertDatabaseHas('royalties', ['artist_id' => $other->id, 'year' => 2026, 'month' => 9]);
    }
}
