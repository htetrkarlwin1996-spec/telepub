<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlbumController extends Controller
{
    public function index(Request $request): Response
    {
        $albums = Album::query()
            ->with(['user'])
            ->withCount('songs')
            ->latest()
            ->paginate(15)
            ->through(function ($album) {
                return [
                    'id' => $album->id,
                    'album_name' => $album->album_name,
                    'artist_name' => $album->artist_name,
                    'status' => $album->status,
                    'registered_date' => optional($album->registered_date)->format('Y-m-d'),
                    'songs_count' => $album->songs_count,
                    'user' => [
                        'id' => $album->user->id,
                        'name' => $album->user->name,
                        'email' => $album->user->email,
                    ],
                    'created_at' => optional($album->created_at)->format('Y-m-d'),
                ];
            });

        return Inertia::render('Admin/Albums/Index', [
            'albums' => $albums,
            'users' => User::where('role', 'user')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'album_name' => ['required', 'string', 'max:255'],
            'artist_name' => ['nullable', 'string', 'max:255'],
            'registered_date' => ['nullable', 'date'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        Album::create([
            'user_id' => $validated['user_id'],
            'album_name' => $validated['album_name'],
            'artist_name' => $validated['artist_name'] ?? null,
            'registered_date' => $validated['registered_date'] ?? null,
            'admin_note' => $validated['admin_note'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Album created successfully.');
    }

    public function show(Album $album): Response
    {
        $album->load(['user', 'songs']);

        return Inertia::render('Admin/Albums/Show', [
            'album' => [
                'id' => $album->id,
                'album_name' => $album->album_name,
                'artist_name' => $album->artist_name,
                'status' => $album->status,
                'registered_date' => optional($album->registered_date)->format('Y-m-d'),
                'admin_note' => $album->admin_note,
                'user' => [
                    'id' => $album->user->id,
                    'name' => $album->user->name,
                    'email' => $album->user->email,
                ],
                'songs' => $album->songs->map(fn ($song) => [
                    'id' => $song->id,
                    'song_name' => $song->song_name,
                    'artist_name' => $song->artist_name,
                    'registered_date' => optional($song->registered_date)->format('Y-m-d'),
                    'bmi_work_id' => $song->bmi_work_id,
                    'bmi_ipi_name' => $song->bmi_ipi_name,
                ]),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $validated = $request->validate([
            'album_name' => ['required', 'string', 'max:255'],
            'artist_name' => ['nullable', 'string', 'max:255'],
            'registered_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,approved,completed,delivery'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $album->update($validated);

        return back()->with('status', 'Album updated successfully.');
    }

    public function addSong(Request $request, Album $album): RedirectResponse
    {
        $validated = $request->validate([
            'song_name' => ['required', 'string', 'max:255'],
            'artist_name' => ['required', 'string', 'max:255'],
            'registered_date' => ['nullable', 'date'],
            'bmi_work_id' => ['nullable', 'string', 'max:255'],
            'bmi_ipi_name' => ['nullable', 'string', 'max:255'],
        ]);

        Song::create([
            'album_id' => $album->id,
            'user_id' => $album->user_id,
            'song_name' => $validated['song_name'],
            'artist_name' => $validated['artist_name'],
            'registered_date' => $validated['registered_date'] ?? null,
            'bmi_work_id' => $validated['bmi_work_id'] ?? null,
            'bmi_ipi_name' => $validated['bmi_ipi_name'] ?? null,
        ]);

        return back()->with('status', 'Song added successfully.');
    }

    public function updateSong(Request $request, Song $song): RedirectResponse
    {
        $validated = $request->validate([
            'song_name' => ['required', 'string', 'max:255'],
            'artist_name' => ['required', 'string', 'max:255'],
            'registered_date' => ['nullable', 'date'],
            'bmi_work_id' => ['nullable', 'string', 'max:255'],
            'bmi_ipi_name' => ['nullable', 'string', 'max:255'],
        ]);

        $song->update($validated);

        return back()->with('status', 'Song updated successfully.');
    }

    public function destroySong(Song $song): RedirectResponse
    {
        $song->delete();

        return back()->with('status', 'Song deleted successfully.');
    }
}