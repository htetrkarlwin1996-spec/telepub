<?php

namespace Tests\Feature\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_receive_only_otp_and_reach_profile_setup_after_verification(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('otp.verify', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, SendOtp::class);
        Notification::assertNotSentTo($user, VerifyEmail::class);

        $otp = Otp::where('email', $user->email)->where('type', 'registration')->firstOrFail();

        $this->post('/verify-otp', ['otp' => $otp->otp])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $this->actingAs($user->fresh())->get('/dashboard')
            ->assertRedirect(route('artist.setup', absolute: false));

        $this->get('/artist/setup')
            ->assertOk()
            ->assertSee('Artist Profile Setup');
    }
}
