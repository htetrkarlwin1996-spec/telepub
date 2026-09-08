<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => $request->user()->email,
            'status' => session('status'),
        ]);
    }

    public function verify(Request $request, OtpService $otpService)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $verified = $otpService->verify($request->user(), $request->code);

        if (!$verified) {
            return back()->withErrors([
                'code' => 'Invalid or expired OTP code.',
            ]);
        }

        return redirect()->route('dashboard')->with('status', 'Email verified successfully.');
    }

    public function resend(Request $request, OtpService $otpService)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $otpService->sendEmailOtp($request->user());

        return back()->with('status', 'A new OTP code has been sent to your email.');
    }
}