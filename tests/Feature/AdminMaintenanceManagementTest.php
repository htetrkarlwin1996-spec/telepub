<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MaintenanceMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMaintenanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app(MaintenanceMode::class)->disable();

        parent::tearDown();
    }

    public function test_admin_can_enable_and_disable_maintenance_mode_from_the_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.maintenance.enable'), [
                'days' => 0,
                'hours' => 1,
                'minutes' => 30,
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(app(MaintenanceMode::class)->active());
        $this->assertGreaterThan(5300, app(MaintenanceMode::class)->remainingSeconds());
        $this->get('/')->assertServiceUnavailable();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Maintenance ON');

        $this->actingAs($admin)
            ->post(route('admin.maintenance.disable'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertFalse(app(MaintenanceMode::class)->active());
    }

    public function test_admin_login_falls_back_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_non_admin_cannot_change_maintenance_mode(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);

        $this->actingAs($artist)
            ->post(route('admin.maintenance.enable'), [
                'days' => 0,
                'hours' => 1,
                'minutes' => 0,
            ])
            ->assertForbidden();

        $this->assertFalse(app(MaintenanceMode::class)->active());
    }
}
