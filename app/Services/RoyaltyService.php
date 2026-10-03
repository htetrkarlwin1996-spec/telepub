<?php

namespace App\Services;

use App\Models\Royalty;
use Illuminate\Support\Facades\DB;

class RoyaltyService
{
    public function __construct(
        private readonly RoyaltyAllocationService $allocations,
        private readonly UserNotifier $notifier,
    ) {}

    public function create(array $attributes, bool $notify = true): Royalty
    {
        $royalty = DB::transaction(function () use ($attributes) {
            $royalty = Royalty::create($attributes);
            $this->allocations->allocate($royalty);

            return $royalty;
        });
        if ($notify) {
            $this->notifier->royaltyCreated($royalty);
        }

        return $royalty;
    }

    public function notifyBatch(iterable $royalties): void
    {
        $summaries = [];
        foreach ($royalties as $royalty) {
            $royalty->loadMissing('allocations');
            foreach ($royalty->allocations->whereIn('beneficiary_type', ['artist', 'master_account']) as $allocation) {
                $key = $allocation->beneficiary_type.':'.$allocation->beneficiary_id;
                $summaries[$key] ??= [
                    'type' => $allocation->beneficiary_type,
                    'id' => (int) $allocation->beneficiary_id,
                    'amount' => 0.0,
                    'entries' => 0,
                    'currency' => $allocation->currency,
                ];
                $summaries[$key]['amount'] += (float) $allocation->allocated_amount;
                $summaries[$key]['entries']++;
            }
        }
        foreach ($summaries as $summary) {
            $this->notifier->royaltyBatchSummary($summary['type'], $summary['id'], $summary['amount'], $summary['entries'], $summary['currency']);
        }
    }

    public function update(Royalty $royalty, array $attributes): Royalty
    {
        return DB::transaction(function () use ($royalty, $attributes) {
            $this->allocations->reverse($royalty);
            $royalty->update($attributes);
            $this->allocations->allocate($royalty->fresh());

            return $royalty->fresh();
        });
    }
}
