<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\MusicStore;
use App\Models\Royalty;
use App\Models\User;
use App\Services\RevenueSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAccountRevenueSplitTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_artist_share_is_eighty_five_percent(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Default Share']);

        $this->assertSame(85.0, (float) $artist->revenue_share_percentage);
    }

    public function test_royalty_is_allocated_through_platform_master_and_collaborator_hierarchy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $primaryUser = User::factory()->create(['role' => 'artist']);
        $collaboratorUser = User::factory()->create(['role' => 'artist']);
        $primary = Artist::create(['user_id' => $primaryUser->id, 'artist_name' => 'Primary']);
        $collaborator = Artist::create(['user_id' => $collaboratorUser->id, 'artist_name' => 'Collaborator']);
        $master = MasterAccount::create([
            'owner_user_id' => $manager->id, 'name' => 'Example Label',
            'platform_fee_percentage' => 15, 'default_management_fee_percentage' => 20,
            'maximum_management_fee_percentage' => 30,
        ]);
        $master->artists()->attach($primary->id, [
            'access_level' => 'report_only', 'status' => 'active', 'created_by' => $admin->id,
        ]);
        $album = Album::create(['artist_id' => $primary->id, 'title' => 'Split Release']);
        $album->collaboratingArtists()->attach($collaborator->id, ['role' => 'collaborator', 'share_percentage' => 30]);
        app(RevenueSplitService::class)->lock($album, $admin);
        $store = MusicStore::create(['name' => 'Split Store', 'slug' => 'split-store']);

        $royalty = Royalty::create([
            'artist_id' => $primary->id, 'album_id' => $album->id, 'store_id' => $store->id,
            'month' => 9, 'year' => 2026, 'amount' => 100, 'currency' => 'USD',
            'royalty_type' => 'royalties', 'entered_by' => $admin->id,
        ]);

        $amounts = $royalty->allocations()->pluck('allocated_amount', 'share_type')->map(fn ($amount) => (float) $amount);
        $this->assertSame(15.0, $amounts['platform_fee']);
        $this->assertSame(17.0, $amounts['management_fee']);
        $this->assertSame(20.4, $amounts['collaborator']);
        $this->assertSame(47.6, $amounts['primary_artist']);
        $this->assertSame(17.0, (float) $master->fresh()->available_balance);
        $this->assertSame(20.4, (float) $collaborator->fresh()->available_balance);
        $this->assertSame(47.6, (float) $primary->fresh()->available_balance);
    }

    public function test_report_only_artist_cannot_create_releases(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $artistUser->id, 'artist_name' => 'Reports Only']);
        $master = MasterAccount::create(['owner_user_id' => $manager->id, 'name' => 'Label']);
        $master->artists()->attach($artist->id, ['access_level' => 'report_only', 'status' => 'active']);

        $this->actingAs($artistUser)->get(route('artist.catalog.create'))->assertForbidden();
    }
}
