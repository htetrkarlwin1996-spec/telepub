<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use App\Notifications\SendOtp;
use App\Services\AdminNotifier;
use App\Services\UserNotifier;
use App\Services\CredentialRevoker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request, AdminNotifier $notifier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'artist',
        ]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Otp::create([
            'email' => $user->email,
            'otp' => $otp,
            'type' => 'registration',
            'expires_at' => now()->addMinutes(10),
        ]);
        $user->notify(new SendOtp($otp, 'registration'));
        $notifier->activity('user_registered', 'New API user registered', $user->name.' created a TeleMusic account through the API.', route('admin.artists'), [
            'Name' => $user->name, 'Email' => $user->email, 'Role' => ucfirst($user->role),
        ]);

        return response()->json([
            'data' => $user->load('artist'),
            'message' => 'Registration successful. Verify the OTP sent to your email before signing in.',
        ], 202);
    }

    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);
        $record = Otp::where('email', $validated['email'])->where('type', 'registration')
            ->whereNull('used_at')->latest()->first();
        if (! $record || ! $record->isValid() || ! hash_equals($record->otp, $validated['otp'])) {
            $record?->recordFailedAttempt();
            throw ValidationException::withMessages(['otp' => ['The OTP code is invalid or has expired.']]);
        }
        $user = User::where('email', $validated['email'])->firstOrFail();
        $record->markAsUsed();
        $user->markEmailAsVerified();
        $user->tokens()->delete();

        return response()->json([
            'data' => $user->load('artist'),
            'token' => $user->createToken('api-token')->plainTextToken,
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Login with email + password.
     */
    public function login(Request $request, UserNotifier $notifier): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Verify your email before signing in.'], 403);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;
        $notifier->login($user, $request->ip(), $request->userAgent());

        return response()->json([
            'data' => $user->load('artist'),
            'token' => $token,
            'message' => 'Login successful.',
        ]);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('artist');

        return response()->json(['data' => $user]);
    }

    /**
     * Update authenticated user profile.
     */
    public function updateUser(Request $request, AdminNotifier $notifier, UserNotifier $userNotifier): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
        ]);

        $user->update($validated);
        $notifier->activity('user_profile_updated', 'API user profile updated', $user->name.' updated their account profile.', route('admin.artists'), [
            'Name' => $user->name, 'Email' => $user->email,
        ]);
        $userNotifier->activity($user, 'Profile updated', 'Your TeleMusic account profile was updated successfully.', route('profile.edit'), 'Review Profile');

        return response()->json([
            'data' => $user->fresh()->load('artist'),
            'message' => 'Profile updated.',
        ]);
    }

    /**
     * Send password reset OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email.']);
        }

        return response()->json(['message' => __($status)], 400);
    }

    /**
     * Reset password with token.
     */
    public function resetPassword(Request $request, UserNotifier $notifier, CredentialRevoker $revoker): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($notifier, $revoker) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
                $revoker->revoke($user);
                $notifier->activity($user, 'Password changed', 'Your TeleMusic password was reset successfully.', route('login'), 'Sign In');
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset.']);
        }

        return response()->json(['message' => __($status)], 400);
    }
}
