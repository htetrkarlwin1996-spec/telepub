<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isImpersonating = $request->hasSession() && $request->session()->has('impersonator_admin_id');
        if (! $user || $user->is_active || $isImpersonating) {
            return $next($request);
        }

        $user->tokens()->delete();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => 'Account is deactivated.']);
    }
}
