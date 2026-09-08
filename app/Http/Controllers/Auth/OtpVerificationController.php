<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    public function create(Request $request): View
    {
        if (! $request->session()->has('otp_email') || ! $request->session()->has('otp_type')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('otp_email'),
            'type' => $request->session()->get('otp_type'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->session()->get('otp_email');
        $type = $request->session()->get('otp_type');

        if (! $email || ! $type) {
            return redirect()->route('login');
        }

        $otpRecord = Otp::where('email', $email)
            ->where('type', $type)
            ->where('otp', $request->otp)
            ->latest()
            ->first();

        if (! $otpRecord || ! $otpRecord->isValid()) {
            throw ValidationException::withMessages([
                'otp' => __('The OTP code is invalid or has expired.'),
            ]);
        }

        $otpRecord->markAsUsed();

        if ($type === 'registration') {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->markEmailAsVerified();
            }
            $request->session()->forget(['otp_email', 'otp_type']);
            return redirect()->route('dashboard')->with('status', 'Email verified successfully!');
        }

        if ($type === 'password_reset') {
            $request->session()->put('password_reset_verified', true);
            $request->session()->put('password_reset_email', $email);
            $request->session()->forget(['otp_email', 'otp_type']);
            return redirect()->route('password.reset.otp');
        }

        return redirect()->route('login');
    }

    public function showResetForm(Request $request): View
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect()->route('login');
        }

        return view('auth.reset-password-otp');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('password_reset_email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $request->session()->forget(['password_reset_verified', 'password_reset_email']);

        return redirect()->route('login')->with('status', 'Password reset successfully. Please login with your new password.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('otp_email');
        $type = $request->session()->get('otp_type');

        if (! $email || ! $type) {
            return redirect()->route('login');
        }

        // Invalidate old OTPs
        Otp::where('email', $email)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate new OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $email,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->notify(new SendOtp($otp, $type));
        }

        return back()->with('status', 'A new OTP code has been sent to your email.');
    }
}
