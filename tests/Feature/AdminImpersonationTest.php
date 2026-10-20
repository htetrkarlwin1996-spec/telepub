<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_as_artist_and_return_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $artistUser = User::factory()->create(['role' => 'artist', 'is_active' => true]);
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Impersonated Artist',
            'revenue_share_percentage' => 70,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.artists.impersonate', $artist))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('impersonator_admin_id', $admin->id)
            ->assertSessionHas('impersonated_artist_id', $artist->id);

        $this->assertAuthenticatedAs($artistUser);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Viewing as Artist: Impersonated Artist')
            ->assertSee('Return to Admin');

        $this->get('/admin/dashboard')->assertForbidden();

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.artists'))
            ->assertSessionMissing('impersonator_admin_id')
            ->assertSessionMissing('impersonated_artist_id');

        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/artists')->assertOk()->assertSee('Login As User');
    }

    public function test_artist_cannot_start_or_fake_an_impersonation_session(): void
    {
        $artistUser = User::factory()->create(['role' => 'artist', 'is_active' => true]);
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Regular Artist',
            'revenue_share_percentage' => 70,
        ]);

        $this->actingAs($artistUser)
            ->post('/admin/artists/'.$artist->id.'/impersonate')
            ->assertForbidden();

        $this->post(route('impersonation.stop'))->assertForbidden();
    }

    public function test_admin_can_impersonate_a_legacy_user_with_an_artist_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $legacyUser = User::factory()->unverified()->create(['role' => 'user', 'is_active' => true]);
        $artist = Artist::create([
            'user_id' => $legacyUser->id,
            'artist_name' => 'Legacy Artist',
            'revenue_share_percentage' => 70,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.artists.impersonate', $artist))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($legacyUser);
        $this->assertNotNull($legacyUser->fresh()->email_verified_at);
        $this->get('/dashboard')->assertOk();
        $this->get('/artist/catalog')->assertOk();
    }

    public function test_admin_can_impersonate_an_artist_through_its_master_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $account = MasterAccount::create(['owner_user_id' => $manager->id, 'name' => 'One Distributor']);
        $artist = Artist::create(['artist_name' => 'Managed Artist', 'created_by_user_id' => $manager->id]);
        $account->artists()->attach($artist->id, ['access_level' => 'full_access', 'status' => 'active', 'created_by' => $manager->id]);

        $this->actingAs($admin)->post(route('admin.artists.impersonate', $artist))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('managed_artist_id', $artist->id);

        $this->assertAuthenticatedAs($manager);
        $this->get('/artist/catalog')->assertOk()->assertSee('Managed Artist');
    }

    public function test_admin_can_login_as_master_account_owner_and_return_to_master_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $account = MasterAccount::create(['owner_user_id' => $manager->id, 'name' => 'Distributor Account']);

        $this->actingAs($admin)->post(route('admin.master-accounts.impersonate', $account))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('impersonated_master_account_id', $account->id);

        $this->assertAuthenticatedAs($manager);
        $this->get(route('manager.dashboard'))->assertOk()->assertSee('Viewing as Master Account: Distributor Account');

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.master-accounts.index'))
            ->assertSessionMissing('impersonated_master_account_id');
        $this->assertAuthenticatedAs($admin);
    }
}
