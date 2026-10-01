<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Payout;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class WithdrawalLifecycle
{
    public function approve(Withdrawal $withdrawal, ?int $adminId, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminId, $note) {
            $locked = Withdrawal::lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless($locked->status === 'pending', 422, 'Only pending withdrawals may be approved.');
            $locked->update(['status' => 'approved', 'admin_notes' => $note, 'processed_by' => $adminId]);

            return $locked->fresh();
        });
    }

    public function complete(Withdrawal $withdrawal, ?int $adminId, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminId, $note) {
            $locked = Withdrawal::lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless($locked->status === 'approved', 422, 'Only approved withdrawals may be completed.');
            $artist = Artist::lockForUpdate()->findOrFail($locked->artist_id);
            $artist->update(['pending_balance' => max(0, (float) $artist->pending_balance - (float) $locked->amount)]);
            $locked->update([
                'status' => 'completed', 'processed_at' => now(),
                'processed_by' => $adminId, 'admin_notes' => $note,
            ]);
            Payout::create([
                'artist_id' => $locked->artist_id, 'withdrawal_id' => $locked->id,
                'invoice_number' => 'PAY-'.strtoupper(uniqid()),
                'amount' => $locked->amount, 'fee' => $locked->fee, 'total' => $locked->total,
                'currency' => $locked->currency, 'status' => 'paid', 'paid_at' => now(),
                'payment_method' => $locked->payment_method, 'processed_by' => $adminId,
            ]);

            return $locked->fresh();
        });
    }

    public function reject(Withdrawal $withdrawal, ?int $adminId, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminId, $note) {
            $locked = Withdrawal::lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless(in_array($locked->status, ['pending', 'approved', 'processing'], true), 422, 'This withdrawal can no longer be rejected.');
            $artist = Artist::lockForUpdate()->findOrFail($locked->artist_id);
            $artist->update([
                'available_balance' => (float) $artist->available_balance + (float) $locked->amount,
                'pending_balance' => max(0, (float) $artist->pending_balance - (float) $locked->amount),
            ]);
            $locked->update([
                'status' => 'rejected', 'admin_notes' => $note,
                'processed_by' => $adminId, 'processed_at' => now(),
            ]);

            return $locked->fresh();
        });
    }
}
