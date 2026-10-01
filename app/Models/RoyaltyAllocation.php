<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoyaltyAllocation extends Model
{
    protected $fillable = [
        'royalty_id', 'split_version_id', 'beneficiary_type', 'beneficiary_id', 'share_type',
        'percentage', 'gross_amount', 'allocated_amount', 'currency',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'gross_amount' => 'decimal:10',
        'allocated_amount' => 'decimal:10',
    ];

    public function royalty()
    {
        return $this->belongsTo(Royalty::class);
    }
}
