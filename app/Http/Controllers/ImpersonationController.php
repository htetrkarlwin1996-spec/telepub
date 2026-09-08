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

        $artist->loadMissing('user');
        abort_unless($artist->user && $artist->user->isArtist(), 404);

        $request->session()->put([
            'impersonator_admin_id' => $admin->id,
            'impersonated_artist_id' => $artist->id,
        ]);

        Auth::guard('web')->login($artist->user);
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

        $request->session()->forget(['impersonator_admin_id', 'impersonated_artist_id']);
        Auth::guard('web')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.artists')
            ->with('success', 'Returned to your admin account.');
    }
}
