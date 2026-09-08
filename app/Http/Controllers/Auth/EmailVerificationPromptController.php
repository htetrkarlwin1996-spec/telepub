<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Notifications\SendOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Redirect unverified users to the OTP verification page.
     * If no OTP is pending, generate and send a new one.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // If there's already a pending OTP in session (e.g., from registration), go straight to OTP page
        if ($request->session()->has('otp_email')) {
            return redirect()->route('otp.verify');
        }

        // Invalidate any existing unused OTPs for this email
        Otp::where('email', $user->email)
            ->where('type', 'registration')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate a new OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'registration',
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send the OTP via email
        $user->notify(new SendOtp($otp, 'registration'));

        // Store OTP context in session so the verify page knows which email/type to check
        session()->put('otp_email', $user->email);
        session()->put('otp_type', 'registration');

        return redirect()->route('otp.verify');
    }
}
