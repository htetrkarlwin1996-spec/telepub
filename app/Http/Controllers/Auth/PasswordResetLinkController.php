<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('We could not find a user with that email address.')]);
        }

        // Invalidate old unused OTPs
        Otp::where('email', $request->email)
            ->where('type', 'password_reset')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $request->email,
            'otp' => $otp,
            'type' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtp($otp, 'password_reset'));

        session()->put('otp_email', $request->email);
        session()->put('otp_type', 'password_reset');

        return redirect()->route('otp.verify')
            ->with('status', 'An OTP code has been sent to your email.');
    }
}
