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
}
