<?php

namespace Tests\Feature;

use App\Models\Artist;
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
}
