<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    /**
     * Get the authenticated user's artist profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile not found. Setup required.'], 404);
        }

        return response()->json(['data' => $artist->load('user')]);
    }

    /**
     * Update artist profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile not found.'], 404);
        }

        $validated = $request->validate([
            'artist_name'            => 'sometimes|string|max:255',
            'genre'                  => 'nullable|string|max:255',
            'bio'                    => 'nullable|string',
            'country'                => 'nullable|string|max:100',
            'avatar'                 => 'nullable|string',
            'banner'                 => 'nullable|string',
            'payment_email'          => 'nullable|email|max:255',
            'paypal_email'           => 'nullable|email|max:255',
            'bank_account_name'      => 'nullable|string|max:255',
            'bank_name'              => 'nullable|string|max:255',
            'bank_country'           => 'nullable|string|max:100',
            'bank_account_no'        => 'nullable|string|max:100',
            'kbz_pay_name'           => 'nullable|string|max:255',
            'kbz_pay_phone'          => 'nullable|string|max:20',
            'wave_pay_name'          => 'nullable|string|max:255',
            'wave_pay_phone'         => 'nullable|string|max:20',
            'spotify_profile_url'    => 'nullable|url|max:500',
            'apple_music_profile_url'=> 'nullable|url|max:500',
            'youtube_profile_url'    => 'nullable|url|max:500',
            'tidal_profile_url'      => 'nullable|url|max:500',
        ]);

        $artist->update($validated);

        return response()->json([
            'data'    => $artist->fresh()->load('user'),
            'message' => 'Profile updated.',
        ]);
    }

    /**
     * Create / initialize artist profile (first-time setup).
     */
    public function setup(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->artist) {
            return response()->json(['message' => 'Artist profile already exists.'], 409);
        }

        $validated = $request->validate([
            'artist_name'  => 'required|string|max:255',
            'genre'        => 'nullable|string|max:255',
            'bio'          => 'nullable|string',
            'country'      => 'nullable|string|max:100',
        ]);

        $artist = $user->artist()->create($validated);

        return response()->json([
            'data'    => $artist->load('user'),
            'message' => 'Artist profile created.',
        ], 201);
    }
}
