<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAccount extends Model
{
    protected $fillable = [
        'owner_user_id', 'name', 'logo', 'country', 'email', 'phone',
        'platform_fee_percentage', 'default_management_fee_percentage',
        'maximum_management_fee_percentage', 'total_earnings', 'available_balance', 'is_active',
    ];

    protected $casts = [
        'platform_fee_percentage' => 'decimal:2',
        'default_management_fee_percentage' => 'decimal:2',
        'maximum_management_fee_percentage' => 'decimal:2',
        'total_earnings' => 'decimal:10',
        'available_balance' => 'decimal:10',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'master_account_artist')
            ->withPivot(['management_fee_percentage', 'access_level', 'status', 'created_by'])
            ->withTimestamps();
    }

    public function managementFeeFor(Artist $artist): float
    {
        $pivot = $this->artists()->whereKey($artist->id)->first()?->pivot;
        $fee = $pivot?->management_fee_percentage ?? $this->default_management_fee_percentage;

        return min((float) $fee, (float) $this->maximum_management_fee_percentage);
    }
}
