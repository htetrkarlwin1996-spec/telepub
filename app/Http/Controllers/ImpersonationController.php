<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, Artist $artist): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin?->isAdmin(), 403);

        $artist->loadMissing(['user', 'masterAccounts.owner']);
        $loginUser = $artist->user ?: $artist->masterAccounts
            ->first(fn ($account) => $account->pivot->status === 'active' && $account->owner?->is_active)?->owner;
        abort_unless($loginUser, 404);
        abort_unless($loginUser->is_active, 403);

        // An administrator has already authenticated and explicitly selected this account.
        // Mark legacy accounts verified so impersonation cannot be trapped by the OTP gate.
        if (! $loginUser->hasVerifiedEmail()) {
            $loginUser->markEmailAsVerified();
        }

        $request->session()->put([
            'impersonator_admin_id' => $admin->id,
            'impersonated_artist_id' => $artist->id,
            'managed_artist_id' => $artist->id,
        ]);

        Auth::guard('web')->login($loginUser);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'You are now viewing '.$artist->artist_name.' as the artist.');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_admin_id');
        abort_unless($adminId, 403);

        $admin = User::whereKey($adminId)->where('role', 'admin')->where('is_active', true)->first();
        abort_unless($admin, 403);

        $request->session()->forget(['impersonator_admin_id', 'impersonated_artist_id', 'managed_artist_id']);
        Auth::guard('web')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.artists')
            ->with('success', 'Returned to your admin account.');
    }
}
