<?php

namespace Tests\Feature\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
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

        $this->assertGuest();
        $response->assertRedirect(route('otp.verify', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, SendOtp::class);
        Notification::assertNotSentTo($user, VerifyEmail::class);
        Notification::assertSentOnDemand(AdminActivityNotification::class);

        $otp = Otp::where('email', $user->email)->where('type', 'registration')->firstOrFail();

        $this->post('/verify-otp', ['otp' => $otp->otp])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user->fresh());

        $this->actingAs($user->fresh())->get('/dashboard')
            ->assertRedirect(route('artist.setup', absolute: false));

        $this->get('/artist/setup')
            ->assertOk()
            ->assertSee('Artist Profile Setup');
    }

    public function test_otp_is_invalidated_after_five_wrong_attempts(): void
    {
        $otp = Otp::create([
            'email' => 'locked@example.com',
            'otp' => '123456',
            'type' => 'registration',
            'expires_at' => now()->addMinutes(10),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withSession(['otp_email' => $otp->email, 'otp_type' => 'registration'])
                ->post('/verify-otp', ['otp' => '000000'])
                ->assertSessionHasErrors('otp');
        }

        $this->assertNotNull($otp->fresh()->used_at);
        $this->assertSame(5, $otp->fresh()->failed_attempts);
    }
}
