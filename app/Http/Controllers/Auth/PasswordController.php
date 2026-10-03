<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminNotifier;
use App\Services\UserNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, AdminNotifier $notifier, UserNotifier $userNotifier): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);
        $notifier->activity('user_password_changed', 'User password changed', $request->user()->name.' changed their account password.', route('admin.artists'), [
            'Name' => $request->user()->name,
            'Email' => $request->user()->email,
        ]);
        $userNotifier->activity($request->user(), 'Password changed', 'Your TeleMusic account password was changed successfully.', route('login'), 'Sign In');

        return back()->with('status', 'password-updated');
    }
}
