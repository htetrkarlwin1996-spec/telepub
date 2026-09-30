<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RoyaltyFilteringAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget('app_setting.display_currency');

        parent::tearDown();
    }

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
            ->assertViewHas('artistMonthlyRows', fn ($rows) => (float) $rows->first()->artist_earnings === 70.0);

        $this->actingAs($artistUser)->get('/artist/royalties?year=2025&month=12')
            ->assertOk()
            ->assertSee('Gross Revenue')
            ->assertSee('USD 100.00')
            ->assertSee('Artist Earnings')
            ->assertSee('USD 70.00')
            ->assertViewHas('monthlyRoyalties', fn ($rows) => (float) $rows->first()->gross_total === 100.0 && (float) $rows->first()->total === 70.0)
            ->assertViewHas('royalties', fn ($rows) => $rows->pluck('store_id')->all() === [$oldStore->id]);

        AppSetting::where('key', 'display_currency')->update(['value' => 'EUR']);
        Cache::forget('app_setting.display_currency');
        $this->get('/artist/royalties?year=2025&month=12')
            ->assertOk()
            ->assertSee('EUR 70.00')
            ->assertViewHas('totalRoyalties', 70.0);

        $this->get('/artist/analytics?year=2026&month=4')
            ->assertOk()
            ->assertSee('April 2026')
            ->assertSee('EUR 140.00')
            ->assertViewHas('storeData', fn ($rows) => $rows->pluck('store_id')->all() === [$newStore->id]);
    }

    public function test_royalty_summary_is_limited_and_see_all_pages_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Summary Artist', 'revenue_share_percentage' => 85]);
        $store = MusicStore::create(['name' => 'Summary Store', 'slug' => 'summary-store']);

        foreach (range(1, 30) as $index) {
            Royalty::create([
                'artist_id' => $artist->id,
                'store_id' => $store->id,
                'month' => (($index - 1) % 12) + 1,
                'year' => $index > 12 ? 2026 : 2025,
                'amount' => $index,
                'streams' => $index * 100,
                'currency' => 'USD',
                'royalty_type' => 'royalties',
                'notes' => "Summary row {$index}",
                'entered_by' => $admin->id,
            ]);
        }

        $this->actingAs($artistUser)->get(route('artist.royalties'))
            ->assertOk()
            ->assertSee('See All')
            ->assertViewHas('royalties', fn ($rows) => $rows->count() === 8)
            ->assertViewHas('monthlyRoyalties', fn ($rows) => $rows->count() === 5);

        $this->get(route('artist.royalties.all', 'transactions'))
            ->assertOk()
            ->assertSee('Transaction History')
            ->assertViewHas('items', fn ($items) => $items->total() === 30 && $items->count() === 25);

        $this->get(route('artist.royalties.all', 'months'))
            ->assertOk()
            ->assertSee('Monthly Breakdown')
            ->assertViewHas('items', fn ($items) => $items->total() === 24);
    }
}
