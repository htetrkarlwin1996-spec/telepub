<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPendingArtistSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_google_and_email_accounts_awaiting_artist_setup(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->create([
            'name' => 'Google Pending Artist',
            'email' => 'google-pending@example.com',
            'google_id' => 'google-pending-123',
            'role' => 'artist',
            'is_active' => true,
        ]);
        User::factory()->unverified()->create([
            'name' => 'Email Pending Artist',
            'email' => 'email-pending@example.com',
            'role' => 'artist',
            'is_active' => true,
        ]);
        $completedUser = User::factory()->create([
            'name' => 'Completed Account',
            'email' => 'completed@example.com',
            'google_id' => 'google-completed-123',
            'role' => 'artist',
            'is_active' => true,
        ]);
        Artist::create(['user_id' => $completedUser->id, 'artist_name' => 'Completed Artist']);

        $this->actingAs($admin)
            ->get(route('admin.artists'))
            ->assertOk()
            ->assertSeeText('Pending Artist Setup')
            ->assertSeeText('Google Pending Artist')
            ->assertSeeText('google-pending@example.com')
            ->assertSeeText('Email Pending Artist')
            ->assertSeeText('email-pending@example.com')
            ->assertSeeText('Google')
            ->assertSeeText('Email & Password')
            ->assertSeeText('Not Verified')
            ->assertSeeText('Completed Artist');
    }

    public function test_completed_artist_is_not_listed_as_pending(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user = User::factory()->create([
            'name' => 'Profile Completed User',
            'email' => 'profile-completed@example.com',
            'google_id' => 'google-completed',
            'role' => 'artist',
        ]);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Public Artist Name']);

        $this->actingAs($admin)
            ->get(route('admin.artists'))
            ->assertOk()
            ->assertDontSeeText('Pending Artist Setup')
            ->assertSeeText('Public Artist Name')
            ->assertSeeText('profile-completed@example.com');
    }
}
