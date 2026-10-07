<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.google.client_id' => 'google-client',
            'services.google.client_secret' => 'google-secret',
            'services.google.redirect' => 'https://dashboard.test/auth/google/callback',
        ]);
        Notification::fake();
    }

    public function test_verified_google_user_can_register_and_login(): void
    {
        $this->mockGoogleUser('google-123', 'artist@example.com', true);

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $user = User::where('email', 'artist@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_login_links_an_existing_email_account(): void
    {
        $existing = User::factory()->unverified()->create(['email' => 'existing@example.com']);
        $this->mockGoogleUser('google-existing', 'EXISTING@example.com', true);

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('google-existing', $existing->fresh()->google_id);
        $this->assertNotNull($existing->fresh()->email_verified_at);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->mockGoogleUser('google-unverified', 'unverified@example.com', false);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
    }

    public function test_deactivated_account_cannot_login_with_google(): void
    {
        User::factory()->create(['email' => 'inactive@example.com', 'is_active' => false]);
        $this->mockGoogleUser('google-inactive', 'inactive@example.com', true);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function mockGoogleUser(string $id, string $email, bool $verified): void
    {
        $googleUser = (new GoogleUser)->map([
            'id' => $id,
            'name' => 'Google Artist',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        $googleUser->user = ['email_verified' => $verified];
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }
}
