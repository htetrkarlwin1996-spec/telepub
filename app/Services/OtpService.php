<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function sendEmailOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_otp_code' => Hash::make($code),
            'email_otp_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::raw(
            "Your Music Publishing verification code is: {$code}\n\nThis code will expire in 10 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Verification OTP Code');
            }
        );
    }

    public function verify(User $user, string $code): bool
    {
        if (!$user->email_otp_code || !$user->email_otp_expires_at) {
            return false;
        }

        if (now()->greaterThan($user->email_otp_expires_at)) {
            return false;
        }

        if (!Hash::check($code, $user->email_otp_code)) {
            return false;
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_otp_code' => null,
            'email_otp_expires_at' => null,
        ])->save();

        return true;
    }
}