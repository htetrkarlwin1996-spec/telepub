<?php

namespace Tests\Feature;

use App\Services\MaintenanceMode;
use Tests\TestCase;

class MaintenancePageTest extends TestCase
{
    public function test_maintenance_page_can_be_rendered(): void
    {
        $view = $this->view('errors.503');

        $view->assertSee("We're making things", false);
        $view->assertSee('Maintenance in progress');
        $view->assertSee('HTTP 503');
        $view->assertSee('/images/maintenance-engineer.png', false);
    }

    public function test_public_routes_are_blocked_while_admin_access_remains_available(): void
    {
        $maintenanceMode = app(MaintenanceMode::class);
        $maintenanceMode->enable(3600);

        try {
            $this->get('/')
                ->assertServiceUnavailable()
                ->assertSee("We're making things", false)
                ->assertSee('maintenance-countdown');

            $this->get('/login')->assertOk();

            $this->get('/admin/dashboard')
                ->assertRedirect(route('login'));
        } finally {
            $maintenanceMode->disable();
        }
    }
}
