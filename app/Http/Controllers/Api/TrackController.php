<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * List tracks for a release.
     */
    public function index(Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        $songs = $album->songs()->orderBy('track_number')->get();

        return response()->json(['data' => $songs]);
    }

    /**
     * Batch save tracks (create/update) for a release.
     */
    public function batchSave(Request $request, Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        if ($album->status !== 'draft') {
            return response()->json(['message' => 'Only draft releases can be edited.'], 422);
        }

        $validated = $request->validate([
            'tracks'                    => 'required|array|min:1',
            'tracks.*.id'               => 'nullable|exists:songs,id',
            'tracks.*.title'            => 'required|string|max:255',
            'tracks.*.version'          => 'nullable|string|max:255',
            'tracks.*.track_number'     => 'required|integer|min:1',
            'tracks.*.genre'            => 'nullable|string|max:255',
            'tracks.*.language'         => 'nullable|string|max:50',
            'tracks.*.explicit'         => 'boolean',
            'tracks.*.lyrics'           => 'nullable|string',
            'tracks.*.isrc_code'        => 'nullable|string|max:50',
            'tracks.*.request_new_isrc' => 'boolean',
            'tracks.*.primary_artists'  => 'nullable|array',
            'tracks.*.composers'        => 'nullable|array',
            'tracks.*.producers'        => 'nullable|array',
            'tracks.*.vocals'           => 'nullable|array',
            'tracks.*.featuring'        => 'nullable|array',
        ]);

        $existingIds = $album->songs()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($validated['tracks'] as $trackData) {
            $trackData['artist_id'] = $album->artist_id;
            $trackData['album_id']  = $album->id;
            $trackData['status']    = 'active';

            // Encode credit arrays as JSON
            foreach (['primary_artists', 'composers', 'producers', 'vocals', 'featuring'] as $creditField) {
                if (isset($trackData[$creditField]) && is_array($trackData[$creditField])) {
                    $trackData[$creditField] = $trackData[$creditField];
                }
            }

            if (! empty($trackData['id'])) {
                $song = Song::findOrFail($trackData['id']);
                $song->update($trackData);
                $submittedIds[] = $song->id;
            } else {
                $song = Song::create($trackData);
                $submittedIds[] = $song->id;
            }
        }

        // Delete tracks not in the submitted set
        $toDelete = array_diff($existingIds, $submittedIds);
        if (! empty($toDelete)) {
            Song::whereIn('id', $toDelete)->delete();
        }

        $songs = $album->songs()->orderBy('track_number')->get();

        return response()->json([
            'data'    => $songs,
            'message' => 'Tracks saved.',
        ]);
    }

    /**
     * Delete a track.
     */
    public function destroy(Song $song): JsonResponse
    {
        $album = $song->album;

        if ($album->status !== 'draft') {
            return response()->json(['message' => 'Only draft releases can be edited.'], 422);
        }

        $this->authorizeAccess($album);

        $song->delete();

        return response()->json(['message' => 'Track deleted.']);
    }

    /**
     * Upload audio file.
     */
    public function uploadAudio(Request $request): JsonResponse
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,flac,aac,ogg|max:102400',
            'song_id'    => 'required|exists:songs,id',
        ]);

        $song = Song::findOrFail($request->song_id);
        $album = $song->album;
        $this->authorizeAccess($album);

        $path = $request->file('audio_file')->store('audio', 'public');

        $song->update(['audio_file' => $path]);

        return response()->json([
            'data'    => $song->fresh(),
            'message' => 'Audio uploaded.',
        ]);
    }

    private function authorizeAccess(Album $album): void
    {
        $user = request()->user();

        if ($user->isAdmin()) {
            return;
        }

        $artist = $user->artist;

        if (! $artist) {
            abort(403, 'Unauthorized.');
        }

        $isOwner  = $album->artist_id === $artist->id;
        $isCollab = $album->collaboratingArtists()
            ->where('artist_id', $artist->id)
            ->exists();

        if (! $isOwner && ! $isCollab) {
            abort(403, 'Unauthorized.');
        }
    }
}
