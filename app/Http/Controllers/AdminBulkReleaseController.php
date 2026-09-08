<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Distribution;
use App\Models\MusicStore;
use App\Models\Song;
use App\Services\SpotifyMetadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminBulkReleaseController extends Controller
{
    public function create()
    {
        return view('admin.releases.bulk-create', [
            'artists' => Artist::orderBy('artist_name')->get(),
            'releases' => [],
            'spotifyReferences' => '',
        ]);
    }

    public function fetch(Request $request, SpotifyMetadataService $spotify)
    {
        $validated = $request->validate([
            'spotify_references' => ['required', 'string', 'max:10000'],
        ]);

        $references = collect(preg_split('/[\r\n,]+/', $validated['spotify_references']))
            ->map(fn ($value) => trim($value))->filter()->unique()->values();

        if ($references->count() > 20) {
            return back()->withInput()->withErrors(['spotify_references' => 'You can fetch up to 20 releases at a time.']);
        }

        try {
            $releases = $spotify->fetchMany($references->all());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['spotify_references' => $exception->getMessage()]);
        }

        return view('admin.releases.bulk-create', [
            'artists' => Artist::orderBy('artist_name')->get(),
            'releases' => $releases,
            'spotifyReferences' => $validated['spotify_references'],
        ]);
    }

    public function store(Request $request, SpotifyMetadataService $spotify)
    {
        $validated = $request->validate([
            'releases' => ['required', 'array', 'min:1', 'max:20'],
            'releases.*.artist_id' => ['required', Rule::exists('artists', 'id')],
            'releases.*.spotify_id' => ['required', 'string', 'max:100'],
            'releases.*.title' => ['required', 'string', 'max:255'],
            'releases.*.release_type' => ['required', Rule::in(['single', 'ep', 'album'])],
            'releases.*.release_date' => ['nullable', 'date'],
            'releases.*.cover_url' => ['nullable', 'url', 'max:2048'],
            'releases.*.label' => ['nullable', 'string', 'max:255'],
            'releases.*.upc_code' => ['nullable', 'string', 'max:100'],
            'releases.*.copyright' => ['nullable', 'string', 'max:255'],
            'releases.*.artist_names' => ['nullable', 'array'],
            'releases.*.tracks' => ['required', 'array', 'min:1'],
            'releases.*.tracks.*.title' => ['required', 'string', 'max:255'],
            'releases.*.tracks.*.track_number' => ['required', 'integer', 'min:1'],
            'releases.*.tracks.*.duration' => ['nullable', 'string', 'max:20'],
            'releases.*.tracks.*.explicit' => ['nullable', 'boolean'],
            'releases.*.tracks.*.artist_names' => ['nullable', 'array'],
            'releases.*.tracks.*.isrc_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{2}-?[A-Za-z0-9]{3}-?\d{2}-?\d{5}$/'],
        ], [
            'releases.*.tracks.*.isrc_code.regex' => 'Each ISRC must use a valid format, for example USRC17607839.',
        ]);

        $stores = MusicStore::where('is_active', true)->get();
        if ($stores->isEmpty()) {
            return back()->withInput()->withErrors(['releases' => 'No active music stores are configured.']);
        }

        $created = DB::transaction(function () use ($validated, $spotify, $stores) {
            $count = 0;
            foreach ($validated['releases'] as $release) {
                $artist = Artist::findOrFail($release['artist_id']);
                $now = now();
                $album = Album::create([
                    'artist_id' => $artist->id,
                    'title' => $release['title'],
                    'slug' => Str::slug($release['title']).'-'.Str::lower(Str::random(6)),
                    'release_type' => $release['release_type'],
                    'cover_art' => $spotify->storeCover($release['cover_url'] ?? null, $release['spotify_id']),
                    'label' => $release['label'] ?? null,
                    'release_date' => $release['release_date'] ?? null,
                    'upc_code' => $release['upc_code'] ?? null,
                    'status' => 'approved',
                    'copyright_holder' => $release['copyright'] ?? ($release['label'] ?? $artist->artist_name),
                    'phonogram_right_holder' => $release['copyright'] ?? ($release['label'] ?? $artist->artist_name),
                    'approved_at' => $now,
                    'notes' => 'Imported from Spotify: https://open.spotify.com/album/'.$release['spotify_id'],
                ]);

                foreach ($release['tracks'] as $track) {
                    $trackArtists = $track['artist_names'] ?? ($release['artist_names'] ?? [$artist->artist_name]);
                    $song = Song::create([
                        'album_id' => $album->id,
                        'artist_id' => $artist->id,
                        'title' => $track['title'],
                        'track_number' => $track['track_number'],
                        'duration' => $track['duration'] ?? null,
                        'isrc_code' => strtoupper(str_replace('-', '', $track['isrc_code'])),
                        'explicit' => (bool) ($track['explicit'] ?? false),
                        'primary_artists' => $trackArtists,
                        'composers' => $trackArtists,
                        'status' => 'approved',
                    ]);

                    foreach ($stores as $store) {
                        Distribution::create([
                            'song_id' => $song->id,
                            'album_id' => $album->id,
                            'store_id' => $store->id,
                            'artist_id' => $artist->id,
                            'status' => 'live',
                            'submitted_at' => $now,
                            'approved_at' => $now,
                            'live_at' => $now,
                        ]);
                    }
                }
                $count++;
            }

            return $count;
        });

        return redirect()->route('admin.releases')->with('success', "{$created} releases created and marked distributed.");
    }
}
