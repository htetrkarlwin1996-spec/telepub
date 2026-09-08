<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index()
    {
        $artist = auth()->user()->artist;
        $songs = Song::where('artist_id', $artist->id)->with('album')->latest()->paginate(20);
        return view('artist.songs.index', compact('songs'));
    }

    public function create(Request $request)
    {
        $artist = auth()->user()->artist;
        $albums = Album::where('artist_id', $artist->id)->whereIn('status', ['draft', 'submitted'])->get();
        $selectedAlbum = null;
        if ($request->album_id) {
            $selectedAlbum = Album::find($request->album_id);
        }
        return view('artist.songs.create', compact('albums', 'selectedAlbum'));
    }

    public function store(Request $request)
    {
        $artist = auth()->user()->artist;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'album_id' => 'required|exists:albums,id',
            'track_number' => 'nullable|integer|min:1',
            'duration' => 'nullable|string|max:20',
            'isrc_code' => 'nullable|string|max:50',
            'explicit' => 'boolean',
            'genre' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'composers' => 'nullable|string|max:500',
            'producers' => 'nullable|string|max:500',
            'featuring' => 'nullable|string|max:500',
            'lyrics' => 'nullable|string',
        ]);

        // Store audio file if uploaded
        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('audio', 'public');
        }

        $validated['artist_id'] = $artist->id;
        $validated['status'] = 'draft';

        Song::create($validated);

        return redirect()->route('artist.songs')->with('success', 'Song uploaded successfully.');
    }

    public function show(Song $song)
    {
        $artist = auth()->user()->artist;
        if ($song->artist_id !== $artist->id) {
            abort(403);
        }
        return view('artist.songs.show', compact('song'));
    }

    public function edit(Song $song)
    {
        $artist = auth()->user()->artist;
        if ($song->artist_id !== $artist->id) {
            abort(403);
        }
        $albums = Album::where('artist_id', $artist->id)->get();
        return view('artist.songs.edit', compact('song', 'albums'));
    }

    public function update(Request $request, Song $song)
    {
        $artist = auth()->user()->artist;
        if ($song->artist_id !== $artist->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'album_id' => 'required|exists:albums,id',
            'track_number' => 'nullable|integer|min:1',
            'duration' => 'nullable|string|max:20',
            'isrc_code' => 'nullable|string|max:50',
            'explicit' => 'boolean',
            'genre' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'composers' => 'nullable|string|max:500',
            'producers' => 'nullable|string|max:500',
            'featuring' => 'nullable|string|max:500',
            'lyrics' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('audio', 'public');
        }

        $song->update($validated);

        return redirect()->route('artist.songs')->with('success', 'Song updated successfully.');
    }

    public function destroy(Song $song)
    {
        $artist = auth()->user()->artist;
        if ($song->artist_id !== $artist->id) {
            abort(403);
        }
        $song->delete();
        return redirect()->route('artist.songs')->with('success', 'Song deleted.');
    }
}
