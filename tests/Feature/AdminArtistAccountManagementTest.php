<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminArtistAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_artist_password_and_email_verification_status(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->unverified()->create([
            'role' => 'artist',
            'name' => 'Old Name',
            'email' => 'old-artist@example.com',
            'password' => 'old-password',
        ]);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Old Artist']);

        $this->actingAs($admin)->get(route('admin.artists.edit', $artist))
            ->assertOk()
            ->assertSee('Account &amp; Security', false)
            ->assertSee('Email Verified');

        $this->put(route('admin.artists.update', $artist), [
            'artist_name' => 'New Artist',
            'account_name' => 'New Name',
            'account_email' => 'new-artist@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
            'email_verified' => '1',
        ])->assertRedirect(route('admin.artists.edit', $artist))->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new-artist@example.com', $user->email);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        Notification::assertSentTo($user, UserActivityNotification::class, fn ($notification) => $notification->toMail($user)->subject === '[TeleMusic] Password changed');

        $this->put(route('admin.artists.update', $artist), [
            'artist_name' => 'New Artist',
            'account_name' => 'New Name',
            'account_email' => 'new-artist@example.com',
            'email_verified' => '0',
        ])->assertSessionHas('success');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_managed_artist_without_user_shows_master_account_notice_and_remains_editable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $master = MasterAccount::create(['owner_user_id' => $manager->id, 'name' => 'Example Label']);
        $artist = Artist::create(['artist_name' => 'Managed Artist', 'created_by_user_id' => $manager->id]);
        $master->artists()->attach($artist->id, ['status' => 'active', 'access_level' => 'full_access']);

        $this->actingAs($admin)->get(route('admin.artists.edit', $artist))
            ->assertOk()
            ->assertSee('No direct Artist login account')
            ->assertSee('Example Label');

        $this->put(route('admin.artists.update', $artist), ['artist_name' => 'Managed Artist Updated'])
            ->assertSessionHas('success');
        $this->assertSame('Managed Artist Updated', $artist->fresh()->artist_name);
    }
}
