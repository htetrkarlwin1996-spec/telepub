<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\MusicStore;
use App\Models\RevenueSplitChangeRequest;
use App\Models\Song;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use App\Services\RevenueSplitService;
use App\Services\RoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_profile_update_and_password_change_email_the_user(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'artist', 'password' => 'old-password']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Email Artist']);

        $this->post('/login', ['email' => $user->email, 'password' => 'old-password'])->assertRedirect('/dashboard');
        $this->assertNotificationTitle($user, 'New login to your account');

        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Updated Name', 'email' => $user->email])->assertRedirect(route('profile.edit'));
        $this->assertNotificationTitle($user, 'Profile updated');

        $this->put(route('password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();
        $this->assertNotificationTitle($user, 'Password changed');
    }

    public function test_release_approval_rejection_and_release_date_email_the_artist(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Release Artist']);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Email Release',
            'status' => 'submitted',
            'release_date' => today(),
        ]);
        $song = Song::create(['artist_id' => $artist->id, 'album_id' => $album->id, 'title' => 'Email Track']);

        $this->actingAs($admin)->post(route('admin.releases.approve', $album), [
            'songs' => [$song->id => ['isrc_code' => '']],
        ])->assertRedirect();
        $this->assertNotificationTitle($user, 'Release approved');

        Artisan::call('releases:notify-live');
        $this->assertNotificationTitle($user, 'Your release is out now');
        $this->assertNotNull($album->fresh()->release_notified_at);

        $album->update(['status' => 'submitted']);
        $this->post(route('admin.releases.reject', $album), ['rejection_reason' => 'Artwork must be updated.'])->assertRedirect();
        $this->assertNotificationTitle($user, 'Release needs changes');
    }

    public function test_withdrawal_request_status_changes_and_admin_payout_email_the_artist(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Paid Artist', 'available_balance' => 200]);

        $this->actingAs($user)->post(route('artist.withdrawals.store'), [
            'amount' => 50,
            'payment_method' => 'paypal',
            'payment_details' => $user->email,
        ])->assertRedirect();
        $withdrawal = $artist->withdrawals()->firstOrFail();
        $this->assertNotificationTitle($user, 'Withdrawal request received');

        $this->actingAs($admin)->post(route('admin.withdrawals.approve', $withdrawal))->assertRedirect();
        $this->assertNotificationTitle($user, 'Withdrawal Approved');
        $this->post(route('admin.withdrawals.complete', $withdrawal))->assertRedirect();
        $this->assertNotificationTitle($user, 'Withdrawal Completed');

        $this->post(route('admin.payouts.store'), [
            'artist_id' => $artist->id,
            'amount' => 25,
            'fee' => 1,
            'currency' => 'USD',
            'payment_method' => 'kbz_pay',
            'payment_reference' => 'EMAIL-PAYOUT-1',
        ])->assertRedirect();
        $this->assertNotificationTitle($user, 'Payout created');

        $this->actingAs($user)->post(route('artist.withdrawals.store'), [
            'amount' => 20,
            'payment_method' => 'wave_pay',
            'account_name' => 'Paid Artist',
            'phone' => '0912345678',
        ])->assertRedirect();
        $rejected = $artist->withdrawals()->where('status', 'pending')->latest('id')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.withdrawals.reject', $rejected), ['admin_notes' => 'Details could not be verified.'])->assertRedirect();
        $this->assertNotificationTitle($user, 'Withdrawal Rejected');
    }

    public function test_new_royalty_emails_primary_collaborator_and_master_beneficiaries(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $primaryUser = User::factory()->create(['role' => 'artist']);
        $collaboratorUser = User::factory()->create(['role' => 'artist']);
        $primary = Artist::create(['user_id' => $primaryUser->id, 'artist_name' => 'Primary']);
        $collaborator = Artist::create(['user_id' => $collaboratorUser->id, 'artist_name' => 'Collaborator']);
        $master = MasterAccount::create([
            'owner_user_id' => $manager->id,
            'name' => 'Email Label',
            'platform_fee_percentage' => 15,
            'default_management_fee_percentage' => 20,
            'maximum_management_fee_percentage' => 30,
        ]);
        $master->artists()->attach($primary->id, ['access_level' => 'full_access', 'status' => 'active']);
        $album = Album::create(['artist_id' => $primary->id, 'title' => 'Royalty Release']);
        $album->collaboratingArtists()->attach($collaborator->id, ['role' => 'collaborator', 'share_percentage' => 30]);
        app(RevenueSplitService::class)->lock($album, $admin);
        $store = MusicStore::create(['name' => 'Email Store', 'slug' => 'email-store']);

        app(RoyaltyService::class)->create([
            'artist_id' => $primary->id,
            'album_id' => $album->id,
            'store_id' => $store->id,
            'royalty_type' => 'royalties',
            'month' => 10,
            'year' => 2026,
            'amount' => 100,
            'currency' => 'USD',
            'entered_by' => $admin->id,
        ]);

        $this->assertNotificationTitle($primaryUser, 'New royalty added');
        $this->assertNotificationTitle($collaboratorUser, 'New royalty added');
        $this->assertNotificationTitle($manager, 'New royalty added');
    }

    public function test_approved_collaborator_share_change_emails_all_release_users(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $primaryUser = User::factory()->create(['role' => 'artist']);
        $collaboratorUser = User::factory()->create(['role' => 'artist']);
        $primary = Artist::create(['user_id' => $primaryUser->id, 'artist_name' => 'Primary']);
        $collaborator = Artist::create(['user_id' => $collaboratorUser->id, 'artist_name' => 'Collaborator']);
        $album = Album::create(['artist_id' => $primary->id, 'title' => 'Shared Release']);
        $album->collaboratingArtists()->attach($collaborator->id, ['role' => 'collaborator', 'share_percentage' => 20]);
        $splits = app(RevenueSplitService::class);
        $splits->lock($album, $admin);
        $request = RevenueSplitChangeRequest::create([
            'album_id' => $album->id,
            'requested_by' => $primaryUser->id,
            'current_splits' => $splits->snapshot($album),
            'proposed_splits' => [
                ...$splits->snapshot($album),
                'primary_artist_percentage' => 70,
                'collaborators' => [[
                    'artist_id' => $collaborator->id,
                    'artist_name' => $collaborator->artist_name,
                    'percentage' => 30,
                ]],
            ],
            'reason' => 'Updated agreement',
        ]);

        $this->actingAs($admin)->post(route('admin.revenue-splits.approve', $request), ['admin_note' => 'Approved'])->assertRedirect();

        $this->assertNotificationTitle($primaryUser, 'Collaborator share approved');
        $this->assertNotificationTitle($collaboratorUser, 'Collaborator share approved');
    }

    public function test_admin_created_artist_and_master_accounts_receive_account_emails(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.artists.store'), [
            'name' => 'Created Artist',
            'email' => 'created-artist@example.com',
            'password' => 'password123',
            'artist_name' => 'Created Artist',
            'revenue_share_percentage' => 85,
        ])->assertRedirect();
        $artistUser = User::where('email', 'created-artist@example.com')->firstOrFail();
        $this->assertNotificationTitle($artistUser, 'Artist account created');

        $this->post(route('admin.master-accounts.store'), [
            'name' => 'Created Label',
            'owner_name' => 'Label Owner',
            'owner_email' => 'label-owner@example.com',
            'password' => 'password123',
            'platform_fee_percentage' => 15,
            'default_management_fee_percentage' => 10,
            'maximum_management_fee_percentage' => 20,
        ])->assertRedirect();
        $owner = User::where('email', 'label-owner@example.com')->firstOrFail();
        $this->assertNotificationTitle($owner, 'Master Account created');
    }

    private function assertNotificationTitle(User $user, string $title): void
    {
        Notification::assertSentTo(
            $user,
            UserActivityNotification::class,
            fn (UserActivityNotification $notification) => $notification->toMail($user)->subject === '[TeleMusic] '.$title,
        );
    }
}
