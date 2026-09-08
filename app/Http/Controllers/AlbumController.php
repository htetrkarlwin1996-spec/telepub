<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index()
    {
        $artist = auth()->user()->artist;
        $albums = Album::where('artist_id', $artist->id)->withCount('songs')->latest()->paginate(20);
        return view('artist.albums.index', compact('albums'));
    }

    public function create()
    {
        return view('artist.albums.create');
    }

    public function store(Request $request)
    {
        $artist = auth()->user()->artist;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'upc_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('cover_art')) {
            $validated['cover_art'] = $request->file('cover_art')->store('covers', 'public');
        }

        $validated['artist_id'] = $artist->id;
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(4);
        $validated['status'] = 'draft';

        Album::create($validated);

        return redirect()->route('artist.albums')->with('success', 'Album created successfully.');
    }

    public function edit(Album $album)
    {
        $artist = auth()->user()->artist;
        if ($album->artist_id !== $artist->id) {
            abort(403);
        }
        return view('artist.albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album)
    {
        $artist = auth()->user()->artist;
        if ($album->artist_id !== $artist->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'upc_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:draft,submitted',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('cover_art')) {
            $validated['cover_art'] = $request->file('cover_art')->store('covers', 'public');
        }

        $album->update($validated);

        return redirect()->route('artist.albums')->with('success', 'Album updated successfully.');
    }

    public function show(Album $album)
    {
        $artist = auth()->user()->artist;
        if ($album->artist_id !== $artist->id) {
            abort(403);
        }
        $songs = Song::where('album_id', $album->id)->orderBy('track_number')->get();
        return view('artist.albums.show', compact('album', 'songs'));
    }

    public function destroy(Album $album)
    {
        $artist = auth()->user()->artist;
        if ($album->artist_id !== $artist->id) {
            abort(403);
        }
        $album->delete();
        return redirect()->route('artist.albums')->with('success', 'Album deleted.');
    }
}
