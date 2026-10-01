<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtp;
use App\Services\AdminNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AdminNotifier $notifier): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Generate OTP for email verification
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'registration',
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtp($otp, 'registration'));
        $notifier->activity('user_registered', 'New user registered', $user->name.' created a TeleMusic account.', route('admin.artists'), [
            'Name' => $user->name,
            'Email' => $user->email,
            'Role' => ucfirst($user->role),
        ]);

        session()->put('otp_email', $user->email);
        session()->put('otp_type', 'registration');

        return redirect()->route('otp.verify');
    }
}
