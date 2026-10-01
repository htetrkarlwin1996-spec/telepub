<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Payout;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutWithdrawalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_sees_success_message_after_requesting_a_withdrawal(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Withdrawal Artist', 'available_balance' => 100]);

        $response = $this->actingAs($user)->followingRedirects()->post(route('artist.withdrawals.store'), [
            'amount' => 25,
            'payment_method' => 'paypal',
            'payment_details' => 'artist@example.com',
        ]);

        $response->assertOk()
            ->assertSeeText('Withdrawal request submitted for review.')
            ->assertSeeText('New Withdrawal #1')
            ->assertSeeText('PENDING');
        $this->assertDatabaseHas('withdrawals', [
            'artist_id' => $user->artist->id,
            'amount' => 25,
            'status' => 'pending',
        ]);
    }

    public function test_every_artist_payment_method_creates_a_pending_withdrawal(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Payment Method Artist', 'available_balance' => 500]);
        $methods = [
            'kbz_pay' => ['account_name' => 'Artist Name', 'phone' => '091111111'],
            'wave_pay' => ['account_name' => 'Artist Name', 'phone' => '092222222'],
            'thai_bank_transfer' => ['account_name' => 'Artist Name', 'bank_name' => 'Bangkok Bank', 'account_number' => '123456789'],
            'wire_transfer' => [
                'beneficiary_name' => 'Artist Name', 'bank_name' => 'International Bank',
                'account_number' => 'IBAN123', 'swift_bic' => 'TESTBIC1',
                'bank_address' => 'Bank Address', 'beneficiary_address' => 'Artist Address',
                'bank_country' => 'Thailand',
            ],
            'paypal' => ['payment_details' => 'artist@example.com'],
            'bank_transfer' => ['payment_details' => 'Account 123'],
            'wise' => ['payment_details' => 'wise@example.com'],
            'payoneer' => ['payment_details' => 'payoneer@example.com'],
        ];

        $this->actingAs($user);
        foreach ($methods as $method => $details) {
            $this->post(route('artist.withdrawals.store'), [
                'amount' => 20,
                'payment_method' => $method,
                ...$details,
            ])->assertRedirect(route('artist.withdrawals'))->assertSessionHasNoErrors();

            $this->assertDatabaseHas('withdrawals', [
                'artist_id' => $artist->id,
                'payment_method' => $method,
                'status' => 'pending',
            ]);
        }

        $this->assertSame(count($methods), $artist->withdrawals()->where('status', 'pending')->count());
    }

    public function test_admin_payout_appears_as_a_successful_artist_withdrawal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Paid Artist', 'available_balance' => 100]);

        $this->actingAs($admin)->post(route('admin.payouts.store'), [
            'artist_id' => $artist->id,
            'amount' => 40,
            'fee' => 2,
            'currency' => 'USD',
            'payment_method' => 'kbz_pay',
            'payment_reference' => 'KBZ-12345',
        ])->assertRedirect(route('admin.payouts'))->assertSessionHas('success');

        $this->assertDatabaseHas('withdrawals', [
            'artist_id' => $artist->id,
            'amount' => 40,
            'fee' => 2,
            'total' => 38,
            'status' => 'completed',
            'payment_method' => 'kbz_pay',
        ]);
        $withdrawalId = $artist->withdrawals()->sole()->id;
        $this->assertDatabaseHas('payouts', [
            'artist_id' => $artist->id,
            'withdrawal_id' => $withdrawalId,
            'amount' => 40,
            'status' => 'paid',
        ]);
        $this->assertSame(60.0, (float) $artist->fresh()->available_balance);

        $this->actingAs($user)->get(route('artist.withdrawals'))
            ->assertOk()
            ->assertSeeText('Success')
            ->assertSeeText('KBZ Pay');
    }

    public function test_completing_an_artist_requested_withdrawal_links_its_payout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create([
            'user_id' => $user->id,
            'artist_name' => 'Lifecycle Artist',
            'available_balance' => 60,
            'pending_balance' => 40,
        ]);
        $withdrawal = Withdrawal::create([
            'artist_id' => $artist->id,
            'amount' => 40,
            'fee' => 2,
            'total' => 38,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'wave_pay',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.withdrawals.approve', $withdrawal))->assertRedirect();
        $this->post(route('admin.withdrawals.complete', $withdrawal))->assertRedirect();

        $this->assertSame('completed', $withdrawal->fresh()->status);
        $this->assertSame(0.0, (float) $artist->fresh()->pending_balance);
        $this->assertDatabaseHas('payouts', [
            'artist_id' => $artist->id,
            'withdrawal_id' => $withdrawal->id,
            'status' => 'paid',
        ]);
    }

    public function test_existing_admin_payouts_are_backfilled_into_artist_withdrawal_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Existing Paid Artist']);
        $payout = Payout::create([
            'artist_id' => $artist->id,
            'invoice_number' => 'INV-EXISTING',
            'amount' => 50,
            'fee' => 1,
            'total' => 49,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'wise',
            'payment_reference' => 'WISE-OLD-1',
            'processed_by' => $admin->id,
        ]);
        $migration = require database_path('migrations/2026_10_01_050000_link_payouts_to_withdrawals.php');

        $migration->down();
        $migration->up();

        $payout = $payout->fresh();
        $this->assertNotNull($payout->withdrawal_id);
        $this->assertDatabaseHas('withdrawals', [
            'id' => $payout->withdrawal_id,
            'artist_id' => $artist->id,
            'amount' => 50,
            'status' => 'completed',
            'payment_method' => 'wise',
            'payment_details' => 'WISE-OLD-1',
        ]);
    }
}
