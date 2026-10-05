<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use App\Notifications\SendOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, SendOtp::class);
        $this->assertDatabaseHas('otps', ['email' => $user->email, 'type' => 'password_reset']);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $otp = Otp::where('email', $user->email)->firstOrFail();
        $this->post('/verify-otp', ['otp' => $otp->otp])
            ->assertRedirect(route('password.reset.otp'));
        $this->get('/reset-password-otp')->assertOk();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $otp = Otp::where('email', $user->email)->firstOrFail();
        $this->post('/verify-otp', ['otp' => $otp->otp]);
        $this->post('/reset-password-otp', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
