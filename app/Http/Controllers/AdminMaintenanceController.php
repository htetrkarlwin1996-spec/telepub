<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMaintenanceController extends Controller
{
    public function enable(Request $request, MaintenanceMode $maintenanceMode): RedirectResponse
    {
        if ($maintenanceMode->active()) {
            return back()->with('success', 'Maintenance mode is already enabled.');
        }

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:0', 'max:30'],
            'hours' => ['required', 'integer', 'min:0', 'max:23'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
        ]);

        $durationSeconds = (($validated['days'] * 24 + $validated['hours']) * 60 + $validated['minutes']) * 60;

        if ($durationSeconds < 60) {
            return back()->withErrors(['duration' => 'Set a maintenance duration of at least one minute.']);
        }

        $maintenanceMode->enable($durationSeconds);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Maintenance mode enabled. Public visitors now see the maintenance page.');
    }

    public function disable(MaintenanceMode $maintenanceMode): RedirectResponse
    {
        if (! $maintenanceMode->active()) {
            return back()->with('success', 'Maintenance mode is already disabled.');
        }

        $maintenanceMode->disable();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Maintenance mode disabled. The public site is live again.');
    }
}
