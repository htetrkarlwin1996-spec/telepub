<?php

namespace App\Services;

use App\Models\Royalty;
use Illuminate\Support\Facades\DB;

class RoyaltyService
{
    public function __construct(private readonly RoyaltyAllocationService $allocations) {}

    public function create(array $attributes): Royalty
    {
        return DB::transaction(function () use ($attributes) {
            $royalty = Royalty::create($attributes);
            $this->allocations->allocate($royalty);

            return $royalty;
        });
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
