<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlbumController extends Controller
{
    public function index(Request $request): Response
    {
        $albums = $request->user()
            ->albums()
            ->withCount('songs')
            ->latest()
            ->get()
            ->map(function ($album) {
                return [
                    'id' => $album->id,
                    'album_name' => $album->album_name,
                    'artist_name' => $album->artist_name,
                    'cover_image' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
                    'status' => $album->status,
                    'songs_count' => $album->songs_count,
                    'registered_date' => optional($album->registered_date)->format('Y-m-d'),
                    'admin_note' => $album->admin_note,
                    'music_stores' => $album->music_stores ?? [],
                    'created_at' => optional($album->created_at)->format('Y-m-d'),
                ];
            });

        return Inertia::render('User/Albums/Index', [
            'albums' => $albums,
        ]);
    }

    public function show(Request $request, Album $album): Response
    {
        abort_unless($album->user_id === $request->user()->id, 403);

        $album->load('songs');

        $canViewSongs = in_array($album->status, ['completed', 'delivery'], true);

        return Inertia::render('User/Albums/Show', [
            'album' => [
                'id' => $album->id,
                'album_name' => $album->album_name,
                'artist_name' => $album->artist_name,
                'cover_image' => $album->cover_image ? asset('storage/' . $album->cover_image) : null,
                'status' => $album->status,
                'registered_date' => optional($album->registered_date)->format('Y-m-d'),
                'admin_note' => $album->admin_note,
                'music_stores' => $album->music_stores ?? [],
                'created_at' => optional($album->created_at)->format('Y-m-d'),
                'songs' => $canViewSongs
                    ? $album->songs->map(function ($song) use ($album) {
                        return [
                            'id' => $song->id,
                            'song_name' => $song->song_name,
                            'artist_name' => $song->artist_name,
                            'registered_date' => optional($song->registered_date)->format('Y-m-d'),
                            'bmi_work_id' => $song->bmi_work_id,
                            /*
                             * IPI Name ကို delivery status ဖြစ်မှ user ဘက်ပြမယ်။
                             * completed မှာ IPI Name ကို waiting / delivery after status အနေနဲ့ UI မှာပြမယ်။
                             */
                            'bmi_ipi_name' => $album->status === 'delivery'
                                ? $song->bmi_ipi_name
                                : null,
                        ];
                    })
                    : [],
            ],
        ]);
    }
}
