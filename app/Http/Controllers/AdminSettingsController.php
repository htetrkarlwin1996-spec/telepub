<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ReleasePayment;
use App\Services\MaintenanceMode;
use App\Services\ReleasePricing;
use App\Services\WithdrawalFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(MaintenanceMode $maintenanceMode, ReleasePricing $releasePricing, WithdrawalFee $withdrawalFee): View
    {
        return view('admin.settings.index', [
            'displayCurrency' => display_currency(),
            'maintenanceActive' => $maintenanceMode->active(),
            'maintenanceRemaining' => $maintenanceMode->remainingSeconds(),
            'maintenanceEndsAt' => $maintenanceMode->endsAt(),
            'releasePricing' => $releasePricing->settings(),
            'withdrawalFeePercentage' => $withdrawalFee->percentage(),
            'minimumWithdrawalAmount' => $withdrawalFee->minimumAmount(),
            'pendingOfflinePayments' => ReleasePayment::with(['album.artist', 'user'])->where('provider', 'offline')->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function updateWithdrawalFee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'withdrawal_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_withdrawal_amount' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'withdrawal_fee_percentage'],
            ['value' => (string) $validated['withdrawal_fee_percentage']],
        );
        AppSetting::updateOrCreate(
            ['key' => 'minimum_withdrawal_amount'],
            ['value' => (string) $validated['minimum_withdrawal_amount']],
        );

        return back()->with('success', 'Withdrawal fee and minimum amount updated.');
    }

    public function updateReleasePricing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'release_price_first_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_single_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_ep_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'release_price_album_usd' => ['required', 'numeric', 'min:0', 'max:9999'],
            'usd_to_thb_rate' => ['required', 'numeric', 'min:0.0001'],
            'usd_to_mmk_rate' => ['required', 'numeric', 'min:0.0001'],
            'offline_bank_instructions' => ['required', 'string', 'max:5000'],
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Release pricing and exchange rates updated.');
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_currency' => ['required', 'in:USD,EUR'],
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'display_currency'],
            ['value' => $validated['display_currency']],
        );
        Cache::forget('app_setting.display_currency');

        return back()->with('success', 'Display currency updated across the application. Amounts were not converted.');
    }
}
