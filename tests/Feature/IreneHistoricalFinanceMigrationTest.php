<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IreneHistoricalFinanceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_exact_net_earnings_and_approved_withdrawals_only_once_without_email(): void
    {
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create(['email' => 'irenezinmarmyint20224@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Irene Zin Mar Myint']);
        $migration = require database_path('migrations/2026_09_21_070000_import_irene_approved_royalties_and_withdrawals.php');

        $migration->up();
        $migration->up();

        $artist->refresh();
        $this->assertEquals(85, $artist->revenue_share_percentage);
        $this->assertEquals(5146, $artist->total_earnings);
        $this->assertEquals(0, $artist->available_balance);

        $this->assertSame(20, DB::table('royalties')->where('artist_id', $artist->id)->count());
        $this->assertSame(5, DB::table('withdrawals')->where('artist_id', $artist->id)->where('status', 'approved')->count());
        $this->assertSame(0, DB::table('payouts')->where('artist_id', $artist->id)->count());

        foreach ([3 => 1378, 6 => 876, 7 => 996, 8 => 1173, 9 => 723] as $month => $expected) {
            $gross = (float) DB::table('royalties')->where('artist_id', $artist->id)->where('year', 2026)->where('month', $month)->sum('amount');
            $this->assertEqualsWithDelta($expected, round($gross * 0.85, 2), 0.001);

            $withdrawal = DB::table('withdrawals')->where('artist_id', $artist->id)->where('requested_at', sprintf('2026-%02d-01 00:00:00', $month))->first();
            $this->assertNotNull($withdrawal);
            $this->assertEquals($expected, $withdrawal->amount);
            $this->assertSame('approved', $withdrawal->status);
        }

        $youtubeMarchGross = (float) DB::table('royalties')
            ->join('music_stores', 'music_stores.id', '=', 'royalties.store_id')
            ->where('royalties.artist_id', $artist->id)
            ->where('royalties.year', 2026)
            ->where('royalties.month', 3)
            ->where('music_stores.slug', 'youtube-music')
            ->value('royalties.amount');
        $this->assertEquals(1008, round($youtubeMarchGross * 0.85, 2));

        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_it_completes_historical_kbz_pay_withdrawals_once_without_changing_balance_or_email(): void
    {
        Mail::fake();
        Notification::fake();

        $user = User::factory()->create(['email' => 'irenezinmarmyint20224@gmail.com']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Irene Zin Mar Myint']);
        (require database_path('migrations/2026_09_21_070000_import_irene_approved_royalties_and_withdrawals.php'))->up();
        $migration = require database_path('migrations/2026_09_21_080000_complete_irene_historical_withdrawals.php');
        $migration->up();
        $migration->up();

        $this->assertSame(5, DB::table('withdrawals')->where('artist_id', $artist->id)->where('status', 'completed')->count());
        $this->assertSame(5, DB::table('payouts')->where('artist_id', $artist->id)->where('status', 'paid')->count());
        $this->assertEquals(0, $artist->fresh()->available_balance);
        foreach ([3 => 1378, 6 => 876, 7 => 996, 8 => 1173, 9 => 723] as $month => $amount) {
            $withdrawal = DB::table('withdrawals')->where('artist_id', $artist->id)->where('requested_at', sprintf('2026-%02d-01 00:00:00', $month))->first();
            $this->assertSame('kbz_pay', $withdrawal->payment_method);
            $this->assertSame(['account_name' => 'Yarzar Soe Moe', 'phone' => '09775001977'], json_decode($withdrawal->payment_details, true));
            $this->assertEquals($amount, $withdrawal->total);
            $payout = DB::table('payouts')->where('invoice_number', 'IRENE-2026-'.sprintf('%02d', $month))->first();
            $this->assertEquals($amount, $payout->total);
            $this->assertSame('kbz_pay', $payout->payment_method);
        }
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_completed_historical_withdrawals_leave_no_available_balance_on_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'irenezinmarmyint20224@gmail.com',
            'role' => 'artist',
            'email_verified_at' => now(),
        ]);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Irene Zin Mar Myint']);
        (require database_path('migrations/2026_09_21_070000_import_irene_approved_royalties_and_withdrawals.php'))->up();
        (require database_path('migrations/2026_09_21_080000_complete_irene_historical_withdrawals.php'))->up();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['Available Balance', '$0.00'])
            ->assertSeeInOrder(['Total Earnings', '$5,146.00']);
    }
}
