<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\KnowledgePost;
use App\Models\Song;
use App\Services\ChunkedAudioUpload;
use App\Services\ReleasePricing;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CatalogController extends Controller
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
     * Display a listing of the artist's releases.
     */
    public function index()
    {
        $artist = current_artist();
        $albums = Album::where('artist_id', $artist->id)
            ->with('songs')
            ->latest()
            ->paginate(10);

        return view('artist.catalog.index', compact('albums'));
    }

    /**
     * Display collaborated releases (where this artist is a collaborator).
     */
    public function collaborations()
    {
        $artist = current_artist();
        $albums = $artist->collaboratedAlbums()
            ->with('songs', 'artist')
            ->latest()
            ->paginate(10);

        return view('artist.catalog.collaborations', compact('albums'));
    }

    /**
     * Show the form for creating a new release (Step 1).
     */
    public function create()
    {
        $genres = static::genres();
        $artists = Artist::where('id', '!=', current_artist()->id)
            ->orderBy('artist_name')
            ->get();

        return view('artist.catalog.create-step-1', compact('genres', 'artists'));
    }

    /**
     * Store Step 1: Release info + cover art.
     */
    public function storeStep1(Request $request)
    {
        $artist = current_artist();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'release_date' => 'required|date',
            'cover_art' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'copyright_holder' => 'required|string|max:255',
            'phonogram_right_holder' => 'required|string|max:255',
            // Collaborating artists
            'collaborating_artists' => 'nullable|array',
            'collaborating_artists.*' => 'distinct|exists:artists,id',
            'collaborating_shares' => 'nullable|array',
            'collaborating_shares.*' => 'numeric|gt:0|max:100',
        ]);
        $this->validateCollaboratorShares($validated, $artist->id);

        // Validate image dimensions (3000x3000)
        $image = $request->file('cover_art');
        [$width, $height] = getimagesize($image);
        if ($width !== 3000 || $height !== 3000) {
            return back()->withErrors(['cover_art' => 'Album art must be exactly 3000×3000 pixels. Uploaded image is '.$width.'×'.$height.' pixels.'])->withInput();
        }

        // Store cover art
        $coverPath = $image->store('cover_art', 'public');

        // Create the album as draft
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.uniqid(),
            'release_type' => $validated['release_type'],
            'cover_art' => $coverPath,
            'genre' => $validated['genre'],
            'release_date' => $validated['release_date'],
            'copyright_holder' => $validated['copyright_holder'],
            'phonogram_right_holder' => $validated['phonogram_right_holder'],
            'status' => 'draft',
        ]);

        // Save collaborating artists
        if (! empty($validated['collaborating_artists'])) {
            $pivotData = [];
            foreach ($validated['collaborating_artists'] as $index => $collabArtistId) {
                $pivotData[$collabArtistId] = [
                    'role' => 'collaborator',
                    'share_percentage' => $validated['collaborating_shares'][$index] ?? 0,
                ];
            }
            $album->collaboratingArtists()->sync($pivotData);
        }

        return redirect()->route('artist.catalog.step2', $album)
            ->with('success', 'Step 1 complete! Now add your tracks.');
    }

    /**
     * Show Step 2: Track upload form.
     */
    public function step2(Album $album)
    {
        // Ensure the album belongs to the authenticated artist
        $this->authorizeAlbum($album, true);

        $existingSongs = $album->songs()->orderBy('track_number')->get();

        return view('artist.catalog.create-step-2', compact('album', 'existingSongs'));
    }

    /**
     * Store Step 2: Save tracks.
     */
    /**
     * Extract person entries from a credit field, handling both new object[] and old string[] formats.
     *
     * Each entry from the form is an array: ['name' => '...', 'spotify_url' => '...', ...]
     * Old format (string[]) will be converted to object[] with empty links.
     */
    private function extractCreditEntries(mixed $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        // If it's already an array of person objects (new format from form)
        if (is_array($value)) {
            $entries = [];
            foreach ($value as $item) {
                // Form submits each person as an array with 'name' key
                if (is_array($item) && isset($item['name']) && ! empty(trim($item['name']))) {
                    $entries[] = [
                        'name' => trim($item['name']),
                        'spotify_url' => trim($item['spotify_url'] ?? ''),
                        'apple_music_url' => trim($item['apple_music_url'] ?? ''),
                        'youtube_url' => trim($item['youtube_url'] ?? ''),
                        'tidal_url' => trim($item['tidal_url'] ?? ''),
                    ];
                }
            }

            return ! empty($entries) ? $entries : null;
        }

        // Old format: comma-separated string (fallback for API calls)
        if (is_string($value) && trim($value) !== '') {
            $parts = preg_split('/\s*(?:,|&| and |\/)\s*/', $value);
            $parts = array_map('trim', $parts);
            $parts = array_filter($parts, fn ($v) => ! empty($v));
            $entries = array_map(fn ($name) => [
                'name' => $name,
                'spotify_url' => '',
                'apple_music_url' => '',
                'youtube_url' => '',
                'tidal_url' => '',
            ], array_values($parts));

            return ! empty($entries) ? $entries : null;
        }

        return null;
    }

    public function storeStep2(Request $request, Album $album)
    {
        $this->authorizeAlbum($album, true);

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
            'tracks.*.lyricist' => 'required|array|min:1',
            'tracks.*.lyricist.*.name' => 'required|string|max:500',
            'tracks.*.lyricist.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.lyricist.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.producers' => 'required|array|min:1',
            'tracks.*.producers.*.name' => 'required|string|max:500',
            'tracks.*.producers.*.spotify_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.apple_music_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.youtube_url' => 'nullable|url|max:500',
            'tracks.*.producers.*.tidal_url' => 'nullable|url|max:500',
            'tracks.*.vocals' => 'required|array|min:1',
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
            'tracks.*.lyrics' => 'required|string',
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
            if (isset($trackData['audio_file_path']) && ! empty($trackData['audio_file_path'])) {
                $songData['audio_file'] = $trackData['audio_file_path'];
            } elseif (isset($trackData['audio_file']) && $trackData['audio_file'] instanceof UploadedFile) {
                $songData['audio_file'] = $trackData['audio_file']->store('tracks', 'public');
            }

            Song::create($songData);
        }

        return redirect()->route('artist.catalog.step3', $album)
            ->with('success', 'Step 2 complete! Now set your pricing.');
    }

    /**
     * Show Step 3: Pricing form.
     */
    public function step3(Album $album)
    {
        $this->authorizeAlbum($album, true);

        return view('artist.catalog.create-step-3', compact('album'));
    }

    /**
     * Store Step 3: Save pricing info.
     */
    public function storeStep3(Request $request, Album $album)
    {
        $this->authorizeAlbum($album, true);

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

        return redirect()->route('artist.catalog.step4', $album)
            ->with('success', 'Step 3 complete! Now select stores.');
    }

    /**
     * Show Step 4: Store selection.
     */
    public function step4(Album $album, ReleasePricing $releasePricing)
    {
        $this->authorizeAlbum($album, true);
        $stores = MusicStore::where('is_active', true)->get();
        $pricing = $releasePricing->for($album);
        $addonArticles = KnowledgePost::whereIn('slug', [
            'composer-songwriter-royalties-composition-share',
            'global-performance-royalties-public-performance-share',
            'mechanical-royalties-streaming-downloads',
        ])->where('is_published', true)->get()->keyBy('slug');

        return view('artist.catalog.create-step-4', compact('album', 'stores', 'pricing', 'addonArticles'));
    }

    /**
     * Store Step 4: Save store selections and submit release.
     */
    public function storeStep4(Request $request, Album $album)
    {
        $this->authorizeAlbum($album, true);

        $validated = $request->validate([
            'stores' => 'required|array|min:1',
            'stores.*' => 'exists:music_stores,id',
            'addons' => 'nullable|array',
            'addons.*' => 'string|in:composer_songwriter,global_performance,mechanical',
        ]);

        $album->update([
            'selected_store_ids' => array_values($validated['stores']),
            'selected_addons' => array_values(array_unique($validated['addons'] ?? [])),
            'payment_status' => 'unpaid',
        ]);

        return redirect()->route('artist.catalog.checkout', $album)
            ->with('success', 'Stores saved. Complete checkout to submit your release.');
    }

    /**
     * Display the specified release.
     */
    public function show(Album $album)
    {
        $this->authorizeAlbum($album);
        $album->load('songs', 'distributions.store', 'collaboratingArtists');
        $artists = $album->splitsAreLocked()
            ? Artist::whereKeyNot($album->artist_id)->orderBy('artist_name')->get()
            : collect();

        return view('artist.catalog.show', compact('album', 'artists'));
    }

    /**
     * Show the form for editing the release.
     */
    public function edit(Album $album)
    {
        $this->authorizeAlbum($album, true);
        $genres = static::genres();
        $artists = Artist::where('id', '!=', $album->artist_id)
            ->orderBy('artist_name')
            ->get();
        $album->load('songs', 'collaboratingArtists');

        return view('artist.catalog.edit', compact('album', 'genres', 'artists'));
    }

    /**
     * Update the release.
     */
    public function update(Request $request, Album $album)
    {
        $this->authorizeAlbum($album, true);

        $artist = current_artist();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'release_type' => 'required|in:single,ep,album',
            'genre' => 'required|string|max:100',
            'release_date' => 'required|date',
            'cover_art' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'copyright_holder' => 'required|string|max:255',
            'phonogram_right_holder' => 'required|string|max:255',
            'physical_release_date' => 'nullable|date',
            'price' => 'required|numeric|min:0|max:999.99',
            // Collaborating artists
            'collaborating_artists' => 'nullable|array',
            'collaborating_artists.*' => 'distinct|exists:artists,id',
            'collaborating_shares' => 'nullable|array',
            'collaborating_shares.*' => 'numeric|gt:0|max:100',
        ]);
        if (! $album->splitsAreLocked()) {
            $this->validateCollaboratorShares($validated, $artist->id);
        }

        if ($request->hasFile('cover_art')) {
            $image = $request->file('cover_art');
            [$width, $height] = getimagesize($image);
            if ($width !== 3000 || $height !== 3000) {
                return back()->withErrors(['cover_art' => 'Album art must be exactly 3000×3000 pixels.'])->withInput();
            }

            // Delete old cover art
            if ($album->cover_art) {
                Storage::disk('public')->delete($album->cover_art);
            }
            $validated['cover_art'] = $image->store('cover_art', 'public');
        }

        $album->update($validated);

        // Sync collaborating artists
        if (! $album->splitsAreLocked()) {
            $album->collaboratingArtists()->sync([]);
        }
        if (! $album->splitsAreLocked() && ! empty($validated['collaborating_artists'])) {
            $pivotData = [];
            foreach ($validated['collaborating_artists'] as $index => $collabArtistId) {
                $pivotData[$collabArtistId] = [
                    'role' => 'collaborator',
                    'share_percentage' => $validated['collaborating_shares'][$index] ?? 0,
                ];
            }
            $album->collaboratingArtists()->sync($pivotData);
        }

        return redirect()->route('artist.catalog.show', $album)
            ->with('success', 'Release updated successfully.');
    }

    /**
     * Remove the specified release.
     */
    public function destroy(Album $album)
    {
        $this->authorizeAlbum($album, true);

        // Delete cover art
        if ($album->cover_art) {
            Storage::disk('public')->delete($album->cover_art);
        }

        // Delete audio files
        foreach ($album->songs as $song) {
            if ($song->audio_file) {
                Storage::disk('public')->delete($song->audio_file);
            }
        }

        $album->songs()->delete();
        $album->distributions()->delete();
        $album->delete();

        return redirect()->route('artist.catalog.index')
            ->with('success', 'Release deleted successfully.');
    }

    /**
     * AJAX audio file upload for Step 2.
     * Handles single audio file upload with progress tracking support.
     */
    public function uploadAudio(Request $request, ChunkedAudioUpload $uploader)
    {
        try {
            return response()->json($uploader->handle($request));
        } catch (\Throwable $exception) {
            Log::channel('audio_upload')->error('Artist audio upload request failed.', [
                'user_id' => $request->user()?->id,
                'action' => $request->input('action', 'chunk'),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    /**
     * Ensure the album belongs to the authenticated artist.
     */
    private function authorizeAlbum(Album $album, bool $write = false): void
    {
        $artist = current_artist();

        // Check if this artist is the primary owner
        if ($album->artist_id === $artist->id) {
            return;
        }

        if ($write) {
            abort(403, 'Only the primary artist or their Master Account can modify this release.');
        }

        // Collaborators have read-only access to this album.
        $isCollaborator = $album->collaboratingArtists()
            ->where('artist_id', $artist->id)
            ->exists();

        if (! $isCollaborator) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function validateCollaboratorShares(array $validated, int $primaryArtistId): void
    {
        $artists = array_map('intval', $validated['collaborating_artists'] ?? []);
        $shares = $validated['collaborating_shares'] ?? [];
        if (in_array($primaryArtistId, $artists, true)) {
            throw ValidationException::withMessages(['collaborating_artists' => 'The primary artist cannot also be a collaborator.']);
        }
        if (count($artists) !== count($shares)) {
            throw ValidationException::withMessages(['collaborating_shares' => 'Every collaborator requires a share.']);
        }
        if (array_sum($shares) > 100) {
            throw ValidationException::withMessages(['collaborating_shares' => 'Collaborator shares may not total more than 100%.']);
        }
    }
}
