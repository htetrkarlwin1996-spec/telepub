<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    /**
     * Common music genres for the dropdown.
     */
    public static function genres(): array
    {
        return [
            'Pop', 'Rock', 'Hip Hop', 'R&B', 'Jazz', 'Classical', 'Electronic',
            'Country', 'Blues', 'Reggae', 'Latin', 'Metal', 'Folk', 'Indie',
            'Alternative', 'Soul', 'Funk', 'Gospel', 'Dance', 'K-Pop',
            'Afrobeat', 'Traditional', 'World', 'Other',
        ];
    }

    /**
     * Common countries for the dropdown.
     */
    public static function countries(): array
    {
        return [
            'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Armenia', 'Australia', 'Austria',
            'Bangladesh', 'Belgium', 'Brazil', 'Bulgaria', 'Cambodia', 'Canada', 'Chile', 'China',
            'Colombia', 'Croatia', 'Czech Republic', 'Denmark', 'Egypt', 'Ethiopia', 'Finland',
            'France', 'Germany', 'Ghana', 'Greece', 'Hong Kong', 'Hungary', 'Iceland', 'India',
            'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan',
            'Jordan', 'Kazakhstan', 'Kenya', 'Kuwait', 'Laos', 'Latvia', 'Lebanon', 'Malaysia',
            'Maldives', 'Mexico', 'Mongolia', 'Morocco', 'Myanmar', 'Nepal', 'Netherlands',
            'New Zealand', 'Nigeria', 'Norway', 'Pakistan', 'Palestine', 'Peru', 'Philippines',
            'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Saudi Arabia', 'Senegal',
            'Serbia', 'Singapore', 'Slovakia', 'South Africa', 'South Korea', 'Spain', 'Sri Lanka',
            'Sudan', 'Sweden', 'Switzerland', 'Taiwan', 'Tanzania', 'Thailand', 'Tunisia',
            'Turkey', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom',
            'United States', 'Vietnam', 'Zambia', 'Zimbabwe',
        ];
    }

    public function setup()
    {
        $artist = auth()->user()->artist;
        $genres = static::genres();
        $countries = static::countries();
        return view('artist.setup', compact('artist', 'genres', 'countries'));
    }

    public function updateSetup(Request $request)
    {
        $user = auth()->user();
        $artist = $user->artist;

        $validated = $request->validate([
            'artist_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            // Payment methods
            'payment_email' => 'nullable|email',
            'paypal_email' => 'nullable|email',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_country' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'kbz_pay_name' => 'nullable|string|max:255',
            'kbz_pay_phone' => 'nullable|string|max:50',
            'wave_pay_name' => 'nullable|string|max:255',
            'wave_pay_phone' => 'nullable|string|max:50',
            // Profile links
            'spotify_profile_url' => 'nullable|url|max:500',
            'apple_music_profile_url' => 'nullable|url|max:500',
            'youtube_profile_url' => 'nullable|url|max:500',
            'tidal_profile_url' => 'nullable|url|max:500',
        ]);

        if ($artist) {
            $artist->update($validated);
        } else {
            $artist = Artist::create(array_merge($validated, ['user_id' => $user->id]));
        }

        return redirect()->route('dashboard')->with('success', 'Profile updated successfully.');
    }

    public function profile()
    {
        $artist = auth()->user()->artist;
        $genres = static::genres();
        $countries = static::countries();
        return view('artist.profile', compact('artist', 'genres', 'countries'));
    }

    public function updateProfile(Request $request)
    {
        $artist = auth()->user()->artist;

        $validated = $request->validate([
            'artist_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            // Payment methods
            'payment_email' => 'nullable|email',
            'paypal_email' => 'nullable|email',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_country' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'kbz_pay_name' => 'nullable|string|max:255',
            'kbz_pay_phone' => 'nullable|string|max:50',
            'wave_pay_name' => 'nullable|string|max:255',
            'wave_pay_phone' => 'nullable|string|max:50',
            // Profile links
            'spotify_profile_url' => 'nullable|url|max:500',
            'apple_music_profile_url' => 'nullable|url|max:500',
            'youtube_profile_url' => 'nullable|url|max:500',
            'tidal_profile_url' => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $artist->update($validated);

        return redirect()->route('artist.profile')->with('success', 'Profile updated successfully.');
    }
}
