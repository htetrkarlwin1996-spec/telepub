<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->text('payment_details')->nullable()->change();
        });

        $artist = DB::table('users')->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', 'irenezinmarmyint20224@gmail.com')
            ->select('artists.id')->first();

        if (! $artist) {
            return;
        }

        DB::transaction(function () use ($artist) {
            foreach ([3 => 1378, 6 => 876, 7 => 996, 8 => 1173, 9 => 723] as $month => $amount) {
                $note = "Irene historical earnings and withdrawal import 2026-09-21; month={$month}; approved historical withdrawal.";
                $withdrawal = DB::table('withdrawals')->where('artist_id', $artist->id)
                    ->where('notes', $note)->where('amount', $amount)->first();

                if (! $withdrawal) {
                    continue;
                }

                $date = sprintf('2026-%02d-01 00:00:00', $month);
                DB::table('withdrawals')->where('id', $withdrawal->id)->update([
                    'status' => 'completed',
                    'payment_method' => 'kbz_pay',
                    'payment_details' => json_encode(['account_name' => 'Yarzar Soe Moe', 'phone' => '09775001977']),
                    'admin_notes' => 'Historical KBZ Pay withdrawal confirmed complete.',
                    'processed_at' => $date,
                    'updated_at' => now(),
                ]);

                DB::table('payouts')->updateOrInsert(
                    ['invoice_number' => 'IRENE-2026-'.sprintf('%02d', $month)],
                    [
                        'artist_id' => $artist->id,
                        'amount' => $amount,
                        'fee' => 0,
                        'total' => $amount,
                        'currency' => 'USD',
                        'status' => 'paid',
                        'period_start' => sprintf('2026-%02d-01', $month),
                        'period_end' => sprintf('2026-%02d-01', $month),
                        'paid_at' => $date,
                        'payment_method' => 'kbz_pay',
                        'payment_reference' => 'KBZ Pay 09775001977',
                        'notes' => 'Historical completed withdrawal for Yarzar Soe Moe; withdrawal #'.$withdrawal->id,
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        // Historical payout records are deliberately preserved.
    }
};
