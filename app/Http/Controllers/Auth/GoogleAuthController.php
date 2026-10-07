<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminNotifier;
use App\Services\UserNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 503, 'Google Sign-In is not configured.');

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, AdminNotifier $adminNotifier, UserNotifier $userNotifier): RedirectResponse
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 503, 'Google Sign-In is not configured.');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors(['email' => 'Google Sign-In could not be completed. Please try again.']);
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();
        $emailVerified = filter_var(data_get($googleUser->user, 'email_verified', false), FILTER_VALIDATE_BOOL);

        if ($email === '' || $googleId === '' || ! $emailVerified) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not provide a verified email address.']);
        }

        $user = User::where('google_id', $googleId)->first();
        $emailOwner = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && $emailOwner && ! $user->is($emailOwner)) {
            return redirect()->route('login')->withErrors(['email' => 'This Google account cannot be linked to that email address.']);
        }

        $isNew = false;
        $user ??= $emailOwner;
        if (! $user) {
            $isNew = true;
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::password(40),
            ]);
        }

        if ($user->google_id && ! hash_equals((string) $user->google_id, $googleId)) {
            return redirect()->route('login')->withErrors(['email' => 'This email is already linked to another Google account.']);
        }

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'This account has been deactivated. Please contact support.']);
        }

        $user->forceFill([
            'google_id' => $googleId,
            'email_verified_at' => $user->email_verified_at ?: now(),
            'avatar' => $user->avatar ?: $googleUser->getAvatar(),
        ])->save();

        if ($isNew) {
            $adminNotifier->activity('user_registered', 'New user registered with Google', $user->name.' created a TeleMusic account with Google.', route('admin.artists'), [
                'Name' => $user->name,
                'Email' => $user->email,
                'Role' => ucfirst($user->role),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget(['login_announcement_shown', 'otp_email', 'otp_type']);
        $userNotifier->login($user, $request->ip(), $request->userAgent());

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('dashboard'));
    }
}
