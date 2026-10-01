<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class WithdrawalFee
{
    public function percentage(): float
    {
        if (! Schema::hasTable('app_settings')) {
            return 2.0;
        }

        return max(0, min(100, (float) (AppSetting::where('key', 'withdrawal_fee_percentage')->value('value') ?? 2)));
    }

    public function calculate(float $amount): array
    {
        $fee = round($amount * $this->percentage() / 100, 2);

        return [
            'fee' => $fee,
            'net' => round($amount - $fee, 2),
        ];
    }
}
