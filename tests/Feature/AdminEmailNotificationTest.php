<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use App\Notifications\NewWithdrawalRequest;
use App\Notifications\SendOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.admin_address' => 'info@telemusic.io']);
        Notification::fake();
    }

    public function test_withdrawal_sends_database_and_admin_email_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Email Artist', 'available_balance' => 100]);

        $this->actingAs($user)->post(route('artist.withdrawals.store'), [
            'amount' => 20,
            'payment_method' => 'kbz_pay',
            'account_name' => 'Email Artist',
            'phone' => '091111111',
        ])->assertRedirect(route('artist.withdrawals'));

        Notification::assertSentTo($admin, NewWithdrawalRequest::class);
        Notification::assertSentOnDemand(NewWithdrawalRequest::class, function ($notification, $channels, $notifiable) {
            return $notifiable->routes['mail'] === 'info@telemusic.io' && in_array('mail', $channels, true);
        });
    }

    public function test_support_message_sends_branded_admin_notification(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Support Artist']);

        $this->actingAs($user)->post(route('support.store'), [
            'subject' => 'Release help',
            'message' => 'Please help me review my release metadata.',
        ])->assertRedirect()->assertSessionHas('success');

        Notification::assertSentTo($admin, AdminActivityNotification::class);
        Notification::assertSentOnDemand(AdminActivityNotification::class);

        $notification = new AdminActivityNotification('test', 'Branded email', 'Test message', route('admin.dashboard'), ['Artist' => 'Support Artist']);
        $html = $notification->toMail(new AnonymousNotifiable)->render();
        $this->assertStringContainsString('TeleMusic', $html);
        $this->assertStringContainsString('logo.png', $html);
        $this->assertStringContainsString('Branded email', $html);
        $this->assertStringContainsString('Support Artist', $html);
    }

    public function test_otp_email_uses_the_branded_logo_template(): void
    {
        $html = (new SendOtp('123456', 'registration'))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString('TeleMusic', $html);
        $this->assertStringContainsString('logo.png', $html);
        $this->assertStringContainsString('123456', $html);
    }
}
