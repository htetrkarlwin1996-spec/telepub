<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\MaintenanceMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(MaintenanceMode $maintenanceMode): View
    {
        return view('admin.settings.index', [
            'displayCurrency' => display_currency(),
            'maintenanceActive' => $maintenanceMode->active(),
            'maintenanceRemaining' => $maintenanceMode->remainingSeconds(),
            'maintenanceEndsAt' => $maintenanceMode->endsAt(),
        ]);
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
