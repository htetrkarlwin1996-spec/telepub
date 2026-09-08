<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoyaltyTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_only_sees_their_earnings_while_admin_keeps_gross_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Test Artist',
            'revenue_share_percentage' => 70,
        ]);
        $store = MusicStore::create(['name' => 'Test Store', 'slug' => 'test-store']);

        Sanctum::actingAs($admin);

        foreach (array_keys(Royalty::TYPES) as $type) {
            $this->postJson('/api/admin/royalties', [
                'artist_id' => $artist->id,
                'store_id' => $store->id,
                'royalty_type' => $type,
                'month' => 7,
                'year' => 2026,
                'amount' => 100,
                'currency' => 'USD',
            ])->assertCreated()->assertJsonPath('data.royalty_type', $type);
        }

        $this->assertSame(280.0, (float) $artist->fresh()->available_balance);

        Sanctum::actingAs($artistUser);
        $this->getJson('/api/artist/royalties/summary')
            ->assertOk()
            ->assertJsonPath('data.total_amount', 280)
            ->assertJsonPath('data.total_earnings', 280)
            ->assertJsonPath('data.balance_breakdown.royalties', 70)
            ->assertJsonPath('data.balance_breakdown.publishing_rights', 70)
            ->assertJsonPath('data.balance_breakdown.composer_rights', 70)
            ->assertJsonPath('data.balance_breakdown.mechanical_royalties', 70)
            ->assertJsonPath('data.balance_breakdown.total_balance', 280)
            ->assertJsonMissingPath('data.tele_music_fee')
            ->assertJsonMissingPath('data.balance_breakdown.gross_amount')
            ->assertJsonMissingPath('data.balance_breakdown.telemusic_fee_amount');

        $royalty = Royalty::firstOrFail();

        $this->getJson('/api/artist/royalties')
            ->assertOk()
            ->assertJsonPath('data.0.amount', 70)
            ->assertJsonPath('data.0.earnings', 70);

        $this->getJson('/api/artist/royalties/'.$royalty->id)
            ->assertOk()
            ->assertJsonPath('data.amount', 70)
            ->assertJsonPath('data.earnings', 70)
            ->assertJsonMissingPath('data.artist');

        $this->actingAs($artistUser)->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Earnings')
            ->assertDontSee('Gross Amount')
            ->assertDontSee('TeleMusic Fee');
    }
}
