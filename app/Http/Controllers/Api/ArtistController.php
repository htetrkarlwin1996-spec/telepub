<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Services\AdminNotifier;
use App\Services\UserNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    /**
     * Get the authenticated user's artist profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $artist = current_artist();

        if (! $artist) {
            return response()->json(['message' => 'Artist profile not found. Setup required.'], 404);
        }

        return response()->json(['data' => $artist->load('user')]);
    }

    /**
     * Update artist profile.
     */
    public function updateProfile(Request $request, AdminNotifier $notifier, UserNotifier $userNotifier): JsonResponse
    {
        $artist = current_artist();

        if (! $artist) {
            return response()->json(['message' => 'Artist profile not found.'], 404);
        }

        $validated = $request->validate([
            'artist_name' => 'sometimes|string|max:255',
            'genre' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'avatar' => 'nullable|string',
            'banner' => 'nullable|string',
            'payment_email' => 'nullable|email|max:255',
            'paypal_email' => 'nullable|email|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_country' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'kbz_pay_name' => 'nullable|string|max:255',
            'kbz_pay_phone' => 'nullable|string|max:20',
            'wave_pay_name' => 'nullable|string|max:255',
            'wave_pay_phone' => 'nullable|string|max:20',
            'spotify_profile_url' => 'nullable|url|max:500',
            'apple_music_profile_url' => 'nullable|url|max:500',
            'youtube_profile_url' => 'nullable|url|max:500',
            'tidal_profile_url' => 'nullable|url|max:500',
        ]);

        $artist->update($validated);
        $notifier->activity('artist_profile_updated', 'API artist profile updated', $artist->artist_name.' updated their artist profile.', route('admin.artists.edit', $artist), [
            'Artist' => $artist->artist_name, 'Account email' => $request->user()->email,
        ]);
        $userNotifier->activity($request->user(), 'Artist profile updated', 'Your TeleMusic artist profile was updated successfully.', route('artist.profile'), 'Review Profile');

        return response()->json([
            'data' => $artist->fresh()->load('user'),
            'message' => 'Profile updated.',
        ]);
    }

    /**
     * Create / initialize artist profile (first-time setup).
     */
    public function setup(Request $request, AdminNotifier $notifier, UserNotifier $userNotifier): JsonResponse
    {
        $user = $request->user();

        if (current_artist()) {
            return response()->json(['message' => 'Artist profile already exists.'], 409);
        }
        abort_if($user->isManager(), 403, 'Master Accounts must create artists through the manager workflow.');

        $validated = $request->validate([
            'artist_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
        ]);

        $artist = $user->artist()->create($validated);
        $notifier->activity('artist_profile_created', 'Artist profile created', $artist->artist_name.' created an artist profile.', route('admin.artists.edit', $artist), [
            'Artist' => $artist->artist_name, 'Account email' => $user->email,
        ]);
        $userNotifier->activity($user, 'Artist profile created', 'Your TeleMusic artist profile was created successfully.', route('artist.profile'), 'Review Profile');

        return response()->json([
            'data' => $artist->load('user'),
            'message' => 'Artist profile created.',
        ], 201);
    }
}
