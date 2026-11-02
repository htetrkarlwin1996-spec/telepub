<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistBalanceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_sees_available_balance_on_withdrawal_and_royalty_pages(): void
    {
        $user = User::factory()->create(['role' => 'artist', 'is_active' => true]);
        Artist::create([
            'user_id' => $user->id,
            'artist_name' => 'Balance Artist',
            'available_balance' => 123.45,
            'pending_balance' => 20,
        ]);

        $this->actingAs($user)
            ->get(route('artist.withdrawals'))
            ->assertOk()
            ->assertSeeText('Available Balance')
            ->assertSeeText('USD 123.45')
            ->assertSeeText('Pending Balance')
            ->assertSeeText('USD 20.00');

        $this->get(route('artist.royalties'))
            ->assertOk()
            ->assertSeeText('Available Balance')
            ->assertSeeText('USD 123.45');
    }

    public function test_master_account_sees_the_selected_artists_balance(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $account = MasterAccount::create(['owner_user_id' => $manager->id, 'name' => 'Balance Label']);
        $firstArtist = Artist::create(['artist_name' => 'First Artist', 'available_balance' => 11.11]);
        $secondArtist = Artist::create(['artist_name' => 'Second Artist', 'available_balance' => 222.22]);
        $account->artists()->attach([
            $firstArtist->id => ['access_level' => 'full_access', 'status' => 'active', 'created_by' => $manager->id],
            $secondArtist->id => ['access_level' => 'full_access', 'status' => 'active', 'created_by' => $manager->id],
        ]);

        $this->actingAs($manager)
            ->withSession(['managed_artist_id' => $secondArtist->id])
            ->get(route('artist.withdrawals'))
            ->assertOk()
            ->assertSeeText('USD 222.22')
            ->assertDontSeeText('USD 11.11');

        $this->get(route('artist.royalties'))
            ->assertOk()
            ->assertSeeText('USD 222.22')
            ->assertDontSeeText('USD 11.11');
    }
}
