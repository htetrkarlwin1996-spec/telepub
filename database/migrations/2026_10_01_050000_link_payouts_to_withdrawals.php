<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('withdrawal_id')->nullable()->after('artist_id')->constrained()->nullOnDelete();
            $table->unique('withdrawal_id');
        });

        $linkedWithdrawalIds = [];
        DB::table('payouts')->whereNull('withdrawal_id')->orderBy('id')->chunkById(250, function ($payouts) use (&$linkedWithdrawalIds) {
            foreach ($payouts as $payout) {
                $matchingWithdrawal = DB::table('withdrawals')
                    ->where('artist_id', $payout->artist_id)
                    ->where('status', 'completed')
                    ->where('amount', $payout->amount)
                    ->where('fee', $payout->fee)
                    ->where('total', $payout->total)
                    ->when($payout->paid_at, fn ($query) => $query->where('processed_at', $payout->paid_at))
                    ->when($linkedWithdrawalIds, fn ($query) => $query->whereNotIn('id', $linkedWithdrawalIds))
                    ->first();

                $withdrawalId = $matchingWithdrawal?->id ?? DB::table('withdrawals')->insertGetId([
                    'artist_id' => $payout->artist_id,
                    'amount' => $payout->amount,
                    'fee' => $payout->fee,
                    'total' => $payout->total,
                    'currency' => $payout->currency,
                    'status' => 'completed',
                    'payment_method' => $payout->payment_method ?? 'admin_payout',
                    'payment_details' => $payout->payment_reference,
                    'notes' => $payout->notes,
                    'admin_notes' => 'Backfilled from admin payout #'.$payout->id.'.',
                    'requested_at' => $payout->paid_at ?? $payout->created_at,
                    'processed_at' => $payout->paid_at ?? $payout->created_at,
                    'processed_by' => $payout->processed_by,
                    'created_at' => $payout->created_at,
                    'updated_at' => $payout->updated_at,
                ]);

                DB::table('payouts')->where('id', $payout->id)->update(['withdrawal_id' => $withdrawalId]);
                $linkedWithdrawalIds[] = $withdrawalId;
            }
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropUnique(['withdrawal_id']);
            $table->dropConstrainedForeignId('withdrawal_id');
        });
    }
};
