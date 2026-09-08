<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Song;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminCatalogController extends Controller
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
     * Show Step 1: Release info + cover art (for creating a new release).
     */
    public function createStep1()
    {
        $genres = static::genres();
        $artists = Artist::with('user')->orderBy('artist_name')->get();
        return view('admin.releases.create-step-1', compact('genres', 'artists'));
    }

    /**
     * Show Step 1 pre-filled (for editing an existing release).
     */
    public function editStep1(Album $album)
    {
        $genres = static::genres();
        $artists = Artist::with('user')->orderBy('artist_name')->get();
        $album->load('songs', 'collaboratingArtists');
        return view('admin.releases.create-step-1', compact('genres', 'artists', 'album'));
    }

    /**
     * Store Step 1: Create a new release (draft).
     */
    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'release_date' => 'required|date',
            'cover_art' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'copyright_holder' => 'required|string|max:255',
            'phonogram_right_holder' => 'required|string|max:255',
            // Collaborating artists
            'collaborating_artists' => 'nullable|array',
            'collaborating_artists.*' => 'exists:artists,id|different:artist_id',
            'collaborating_shares' => 'nullable|array',
            'collaborating_shares.*' => 'numeric|min:0|max:100',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_art')) {
            $coverPath = $request->file('cover_art')->store('cover_art', 'public');
        }

        $album = Album::create([
            'artist_id' => $validated['artist_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'release_type' => $validated['release_type'],
            'cover_art' => $coverPath,
            'genre' => $validated['genre'],
            'release_date' => $validated['release_date'],
            'copyright_holder' => $validated['copyright_holder'],
            'phonogram_right_holder' => $validated['phonogram_right_holder'],
            'status' => 'draft',
        ]);

        // Save collaborating artists
        if (!empty($validated['collaborating_artists'])) {
            $pivotData = [];
            foreach ($validated['collaborating_artists'] as $index => $collabArtistId) {
                $pivotData[$collabArtistId] = [
                    'role' => 'collaborator',
                    'share_percentage' => $validated['collaborating_shares'][$index] ?? 0,
                ];
            }
            $album->collaboratingArtists()->sync($pivotData);
        }

        return redirect()->route('admin.releases.step2', $album)
            ->with('success', 'Step 1 complete! Now add your tracks.');
    }

    /**
     * Update Step 1: Update an existing release's info.
     */
    public function updateStep1(Request $request, Album $album)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'release_date' => 'required|date',
            'cover_art' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'copyright_holder' => 'required|string|max:255',
            'phonogram_right_holder' => 'required|string|max:255',
            // Collaborating artists
            'collaborating_artists' => 'nullable|array',
            'collaborating_artists.*' => 'exists:artists,id|different:artist_id',
            'collaborating_shares' => 'nullable|array',
            'collaborating_shares.*' => 'numeric|min:0|max:100',
        ]);

        if ($request->hasFile('cover_art')) {
            // Delete old cover art
            if ($album->cover_art) {
                Storage::disk('public')->delete($album->cover_art);
            }
            $validated['cover_art'] = $request->file('cover_art')->store('cover_art', 'public');
        }

        $album->update($validated);

        // Sync collaborating artists
        $album->collaboratingArtists()->sync([]); // clear existing
        if (!empty($validated['collaborating_artists'])) {
            $pivotData = [];
            foreach ($validated['collaborating_artists'] as $index => $collabArtistId) {
                $pivotData[$collabArtistId] = [
                    'role' => 'collaborator',
                    'share_percentage' => $validated['collaborating_shares'][$index] ?? 0,
                ];
            }
            $album->collaboratingArtists()->sync($pivotData);
        }

        return redirect()->route('admin.releases.step2', $album)
            ->with('success', 'Release info updated! Now manage your tracks.');
    }

    /**
     * Show Step 2: Track upload form.
     */
    public function step2(Album $album)
    {
        $album->load('songs');
        $artist = $album->artist;
        return view('admin.releases.create-step-2', compact('album', 'artist'));
    }

    /**
     * Extract person entries from a credit field, handling both new object[] and old string[] formats.
     */
    private function extractCreditEntries(mixed $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            $entries = [];
            foreach ($value as $item) {
                if (is_array($item) && isset($item['name']) && !empty(trim($item['name']))) {
                    $entries[] = [
                        'name' => trim($item['name']),
                        'spotify_url' => trim($item['spotify_url'] ?? ''),
                        'apple_music_url' => trim($item['apple_music_url'] ?? ''),
                        'youtube_url' => trim($item['youtube_url'] ?? ''),
                        'tidal_url' => trim($item['tidal_url'] ?? ''),
                    ];
                }
            }
            return !empty($entries) ? $entries : null;
        }

        if (is_string($value) && trim($value) !== '') {
            $parts = preg_split('/\s*(?:,|&| and |\/)\s*/', $value);
            $parts = array_map('trim', $parts);
            $parts = array_filter($parts, fn($v) => !empty($v));
            $entries = array_map(fn($name) => [
                'name' => $name,
                'spotify_url' => '',
                'apple_music_url' => '',
                'youtube_url' => '',
                'tidal_url' => '',
            ], array_values($parts));
            return !empty($entries) ? $entries : null;
        }

        return null;
    }

    /**
     * Store Step 2: Save tracks.
     */
    public function storeStep2(Request $request, Album $album)
    {
        $validated = $request->validate([
            'tracks' => 'required|array|min:1',
            'tracks.*.title' => 'required|string|max:255',
            'tracks.*.version' => 'nullable|string|max:255',
            'tracks.*.primary_artists' => 'required|array|min:1',
            'tracks.*.primary_artists.*.name' => 'required|string|max:500',
            'tracks.*.primary_artists.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.primary_artists.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.primary_artists.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.primary_artists.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.composers' => 'required|array|min:1',
            'tracks.*.composers.*.name' => 'required|string|max:500',
            'tracks.*.composers.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.composers.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.composers.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.composers.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.lyricist' => 'nullable|array',
            'tracks.*.lyricist.*.name' => 'required|string|max:500',
            'tracks.*.lyricist.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.producers' => 'nullable|array',
            'tracks.*.producers.*.name' => 'required|string|max:500',
            'tracks.*.producers.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.vocals' => 'nullable|array',
            'tracks.*.vocals.*.name' => 'required|string|max:500',
            'tracks.*.vocals.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.vocals.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.vocals.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.vocals.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.featuring' => 'nullable|array',
            'tracks.*.featuring.*.name' => 'required|string|max:500',
            'tracks.*.featuring.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.featuring.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.featuring.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.featuring.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.isrc_code' => 'nullable|string|max:50',
            'tracks.*.request_new_isrc' => 'boolean',
            'tracks.*.audio_file' => 'nullable|file|mimes:mp3,wav,aac,flac,ogg|max:51200',
            'tracks.*.audio_file_path' => 'nullable|string|max:500',
            'tracks.*.explicit' => 'boolean',
            'tracks.*.language' => 'nullable|string|max:50',
            'tracks.*.lyrics' => 'nullable|string',
            'tracks.*.duration' => 'nullable|integer|min:0',
        ]);

        // Delete existing songs and re-add
        $album->songs()->delete();

        foreach ($validated['tracks'] as $index => $trackData) {
            $trackNumber = $index + 1;

            $songData = [
                'album_id' => $album->id,
                'artist_id' => $album->artist_id,
                'title' => $trackData['title'],
                'version' => $trackData['version'] ?? null,
                'track_number' => $trackNumber,
                'primary_artists' => $this->extractCreditEntries($trackData['primary_artists'] ?? null),
                'composers' => $this->extractCreditEntries($trackData['composers'] ?? null),
                'lyricist' => $this->extractCreditEntries($trackData['lyricist'] ?? null),
                'producers' => $this->extractCreditEntries($trackData['producers'] ?? null),
                'vocals' => $this->extractCreditEntries($trackData['vocals'] ?? null),
                'featuring' => $this->extractCreditEntries($trackData['featuring'] ?? null),
                'isrc_code' => $trackData['isrc_code'] ?? null,
                'request_new_isrc' => $trackData['request_new_isrc'] ?? false,
                'explicit' => $trackData['explicit'] ?? false,
                'language' => $trackData['language'] ?? 'English',
                'lyrics' => $trackData['lyrics'] ?? null,
                'duration' => $trackData['duration'] ?? 0,
                'status' => 'draft',
            ];

            // Handle audio file - AJAX upload path takes priority, then direct upload
            if (isset($trackData['audio_file_path']) && !empty($trackData['audio_file_path'])) {
                $songData['audio_file'] = $trackData['audio_file_path'];
            } elseif (isset($trackData['audio_file']) && $trackData['audio_file'] instanceof \Illuminate\Http\UploadedFile) {
                $songData['audio_file'] = $trackData['audio_file']->store('tracks', 'public');
            }

            Song::create($songData);
        }

        return redirect()->route('admin.releases.step3', $album)
            ->with('success', 'Step 2 complete! Now set your pricing.');
    }

    /**
     * Show Step 3: Pricing form.
     */
    public function step3(Album $album)
    {
        return view('admin.releases.create-step-3', compact('album'));
    }

    /**
     * Store Step 3: Save pricing info.
     */
    public function storeStep3(Request $request, Album $album)
    {
        $validated = $request->validate([
            'release_date' => 'required|date',
            'physical_release_date' => 'nullable|date|after_or_equal:release_date',
            'price' => 'required|numeric|min:0|max:999.99',
        ]);

        $album->update([
            'release_date' => $validated['release_date'],
            'physical_release_date' => $validated['physical_release_date'] ?? null,
            'price' => $validated['price'],
        ]);

        return redirect()->route('admin.releases.step4', $album)
            ->with('success', 'Step 3 complete! Now select stores.');
    }

    /**
     * Show Step 4: Store selection.
     */
    public function step4(Album $album)
    {
        $album->load('songs', 'distributions');
        $stores = MusicStore::where('is_active', true)->get();
        return view('admin.releases.create-step-4', compact('album', 'stores'));
    }

    /**
     * Store Step 4: Save store selections and submit release.
     */
    public function storeStep4(Request $request, Album $album)
    {
        $validated = $request->validate([
            'stores' => 'required|array|min:1',
            'stores.*' => 'exists:music_stores,id',
            'status' => 'nullable|in:draft,submitted,approved',
        ]);

        // Delete existing distributions for this album
        $album->distributions()->delete();

        // Create distributions for each selected store
        $songs = $album->songs;
        foreach ($validated['stores'] as $storeId) {
            foreach ($songs as $song) {
                Distribution::create([
                    'song_id' => $song->id,
                    'album_id' => $album->id,
                    'store_id' => $storeId,
                    'artist_id' => $album->artist_id,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'distribution_fee' => 0.00,
                ]);
            }
        }

        // Update album status (admin can set directly)
        $newStatus = $validated['status'] ?? 'submitted';
        $album->update(['status' => $newStatus]);

        if ($newStatus === 'approved') {
            $album->update(['approved_at' => now()]);
        }

        return redirect()->route('admin.releases.show', $album)
            ->with('success', 'Release ' . $newStatus . ' successfully!');
    }

    /**
     * AJAX audio file upload for Step 2.
     * Handles single audio file upload with progress tracking support.
     */
    public function uploadAudio(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,aac,flac,ogg|max:51200',
        ]);

        $path = $request->file('audio_file')->store('tracks', 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'filename' => basename($path),
        ]);
    }
}
