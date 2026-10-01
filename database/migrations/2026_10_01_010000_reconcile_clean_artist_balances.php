<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The allocation backfill establishes the authoritative lifetime earnings.
        // For artists with no withdrawal or payout history, available balance must
        // equal that full allocation total. Accounts with financial history are
        // intentionally left untouched for a separate ledger-aware reconciliation.
        DB::table('artists')->orderBy('id')->chunkById(250, function ($artists) {
            foreach ($artists as $artist) {
                $hasWithdrawals = DB::table('withdrawals')->where('artist_id', $artist->id)->exists();
                $hasPayouts = DB::table('payouts')->where('artist_id', $artist->id)->exists();

                if ($hasWithdrawals || $hasPayouts) {
                    continue;
                }

                $allocated = (float) DB::table('royalty_allocations')
                    ->where('beneficiary_type', 'artist')
                    ->where('beneficiary_id', $artist->id)
                    ->sum('allocated_amount');

                DB::table('artists')->where('id', $artist->id)->update([
                    'total_earnings' => $allocated,
                    'available_balance' => $allocated,
                    'pending_balance' => 0,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Financial reconciliation is intentionally not reversed.
    }
};
