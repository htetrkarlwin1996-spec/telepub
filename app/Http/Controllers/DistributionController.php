<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Distribution;
use App\Models\MusicStore;
use App\Models\Song;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    public function index()
    {
        $artist = current_artist();
        $distributions = Distribution::where('artist_id', $artist->id)
            ->with(['song', 'store', 'album'])
            ->latest()
            ->paginate(20);

        return view('artist.distributions.index', compact('distributions'));
    }

    public function create()
    {
        $artist = current_artist();
        $songs = Song::where('artist_id', $artist->id)->where('status', 'approved')->get();
        $albums = Album::where('artist_id', $artist->id)->where('status', 'approved')->get();
        $stores = MusicStore::where('is_active', true)->get();

        return view('artist.distributions.create', compact('songs', 'albums', 'stores'));
    }

    public function store(Request $request)
    {
        $artist = current_artist();

        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'store_id' => 'required|exists:music_stores,id',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        // Check if already distributed
        $existing = Distribution::where('song_id', $validated['song_id'])
            ->where('store_id', $validated['store_id'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'This song is already distributed to this store.');
        }

        $validated['artist_id'] = $artist->id;
        $validated['status'] = 'pending';

        Distribution::create($validated);

        return redirect()->route('artist.distributions')->with('success', 'Distribution request submitted.');
    }

    public function show(Distribution $distribution)
    {
        $artist = current_artist();
        if ($distribution->artist_id !== $artist->id) {
            abort(403);
        }

        return view('artist.distributions.show', compact('distribution'));
    }
}
