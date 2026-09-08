<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoyaltyFilteringAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_royalties_are_sorted_by_period_and_can_be_filtered_and_edited_from_analytics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Period Artist',
            'revenue_share_percentage' => 70,
        ]);
        $oldStore = MusicStore::create(['name' => 'Old Period Store', 'slug' => 'old-period-store']);
        $newStore = MusicStore::create(['name' => 'New Period Store', 'slug' => 'new-period-store']);

        Royalty::create([
            'artist_id' => $artist->id, 'store_id' => $newStore->id,
            'month' => 4, 'year' => 2026, 'amount' => 200, 'streams' => 2000,
            'currency' => 'USD', 'royalty_type' => 'royalties', 'entered_by' => $admin->id,
        ]);
        Royalty::create([
            'artist_id' => $artist->id, 'store_id' => $oldStore->id,
            'month' => 12, 'year' => 2025, 'amount' => 100, 'streams' => 1000,
            'currency' => 'USD', 'royalty_type' => 'royalties', 'entered_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/royalties')
            ->assertOk()
            ->assertViewHas('royalties', fn ($rows) => $rows->pluck('store_id')->all() === [$newStore->id, $oldStore->id]);

        $this->get('/admin/royalties?year=2025&month=12')
            ->assertOk()
            ->assertViewHas('royalties', fn ($rows) => $rows->pluck('store_id')->all() === [$oldStore->id]);

        $this->get('/admin/analytics?year=2025&month=12')
            ->assertOk()
            ->assertSee('December 2025')
            ->assertSee('View / Edit Entries')
            ->assertViewHas('periodRows', fn ($rows) => $rows->pluck('store_id')->all() === [$oldStore->id])
            ->assertViewHas('artistMonthlyRows', fn ($rows) => (float) $rows->first()->artist_earnings === 70.0);

        $this->actingAs($artistUser)->get('/artist/royalties?year=2025&month=12')
            ->assertOk()
            ->assertSee('$70.00')
            ->assertViewHas('royalties', fn ($rows) => $rows->pluck('store_id')->all() === [$oldStore->id]);

        $this->get('/artist/analytics?year=2026&month=4')
            ->assertOk()
            ->assertSee('April 2026')
            ->assertSee('$140.00')
            ->assertViewHas('storeData', fn ($rows) => $rows->pluck('store_id')->all() === [$newStore->id]);
    }
}
