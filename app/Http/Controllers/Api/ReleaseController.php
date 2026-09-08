<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReleaseController extends Controller
{
    /**
     * List authenticated artist's releases (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $releases = $artist->albums()
            ->with('artist', 'songs', 'stores', 'collaboratingArtists')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $releases->items(),
            'meta' => [
                'current_page' => $releases->currentPage(),
                'last_page'    => $releases->lastPage(),
                'per_page'     => $releases->perPage(),
                'total'        => $releases->total(),
            ],
        ]);
    }

    /**
     * List releases where the artist is a collaborator.
     */
    public function collaborations(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $releases = $artist->collaboratedAlbums()
            ->with('artist', 'songs', 'stores', 'collaboratingArtists')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $releases->items(),
            'meta' => [
                'current_page' => $releases->currentPage(),
                'last_page'    => $releases->lastPage(),
                'per_page'     => $releases->perPage(),
                'total'        => $releases->total(),
            ],
        ]);
    }

    /**
     * Create a new release (Step 1).
     */
    public function store(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'release_type'           => 'required|in:single,ep,album',
            'genre'                  => 'nullable|string|max:255',
            'label'                  => 'nullable|string|max:255',
            'copyright_holder'       => 'nullable|string|max:255',
            'phonogram_right_holder' => 'nullable|string|max:255',
            'request_new_isrc'       => 'boolean',
            'collaborators'          => 'nullable|array',
            'collaborators.*.id'     => 'required|exists:artists,id',
            'collaborators.*.share_percentage' => 'required|numeric|min:1|max:99',
        ]);

        $album = DB::transaction(function () use ($artist, $validated) {
            $album = $artist->albums()->create([
                'title'                  => $validated['title'],
                'slug'                   => Str::slug($validated['title']).'-'.Str::random(5),
                'release_type'           => $validated['release_type'],
                'genre'                  => $validated['genre'] ?? $artist->genre,
                'label'                  => $validated['label'] ?? null,
                'copyright_holder'       => $validated['copyright_holder'] ?? $artist->artist_name,
                'phonogram_right_holder' => $validated['phonogram_right_holder'] ?? $artist->artist_name,
                'request_new_isrc'       => $validated['request_new_isrc'] ?? false,
                'status'                 => 'draft',
            ]);

            // Sync collaborators
            if (! empty($validated['collaborators'])) {
                $syncData = [];
                foreach ($validated['collaborators'] as $collab) {
                    $syncData[$collab['id']] = [
                        'share_percentage' => $collab['share_percentage'],
                        'role'             => 'collaborator',
                    ];
                }
                $album->collaboratingArtists()->sync($syncData);
            }

            return $album;
        });

        return response()->json([
            'data'    => $album->load('artist', 'collaboratingArtists'),
            'message' => 'Release created.',
        ], 201);
    }

    /**
     * Show release details.
     */
    public function show(Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        return response()->json([
            'data' => $album->load('artist', 'songs', 'stores', 'collaboratingArtists', 'distributions'),
        ]);
    }

    /**
     * Update release.
     */
    public function update(Request $request, Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        if ($album->status !== 'draft') {
            return response()->json(['message' => 'Only draft releases can be edited.'], 422);
        }

        $validated = $request->validate([
            'title'                  => 'sometimes|string|max:255',
            'release_type'           => 'sometimes|in:single,ep,album',
            'genre'                  => 'nullable|string|max:255',
            'label'                  => 'nullable|string|max:255',
            'cover_art'              => 'nullable|string',
            'copyright_holder'       => 'nullable|string|max:255',
            'phonogram_right_holder' => 'nullable|string|max:255',
            'request_new_isrc'       => 'boolean',
            'collaborators'          => 'nullable|array',
            'collaborators.*.id'     => 'required|exists:artists,id',
            'collaborators.*.share_percentage' => 'required|numeric|min:1|max:99',
        ]);

        DB::transaction(function () use ($album, $validated) {
            $album->update($validated);

            if (array_key_exists('collaborators', $validated)) {
                $syncData = [];
                foreach ($validated['collaborators'] as $collab) {
                    $syncData[$collab['id']] = [
                        'share_percentage' => $collab['share_percentage'],
                        'role'             => 'collaborator',
                    ];
                }
                $album->collaboratingArtists()->sync($syncData);
            }
        });

        return response()->json([
            'data'    => $album->fresh()->load('artist', 'collaboratingArtists', 'songs'),
            'message' => 'Release updated.',
        ]);
    }

    /**
     * Delete draft release.
     */
    public function destroy(Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        if ($album->status !== 'draft') {
            return response()->json(['message' => 'Only draft releases can be deleted.'], 422);
        }

        $album->songs()->delete();
        $album->collaboratingArtists()->detach();
        $album->delete();

        return response()->json(['message' => 'Release deleted.']);
    }

    /**
     * Submit release for approval.
     */
    public function submit(Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        if ($album->status !== 'draft') {
            return response()->json(['message' => 'Release already submitted.'], 422);
        }

        // Validation: must have at least one song and one store
        if ($album->songs()->count() === 0) {
            return response()->json(['message' => 'Add at least one track before submitting.'], 422);
        }

        if ($album->distributions()->count() === 0) {
            return response()->json(['message' => 'Select at least one store before submitting.'], 422);
        }

        $album->update([
            'status' => 'submitted',
            'notes'  => request('notes'),
        ]);

        return response()->json([
            'data'    => $album->fresh()->load('artist', 'songs', 'stores'),
            'message' => 'Release submitted for approval.',
        ]);
    }

    /**
     * Update pricing info.
     */
    public function updatePricing(Request $request, Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        $validated = $request->validate([
            'price'                => 'nullable|numeric|min:0',
            'release_date'         => 'nullable|date',
            'physical_release_date' => 'nullable|date|after_or_equal:release_date',
        ]);

        $album->update($validated);

        return response()->json([
            'data'    => $album->fresh(),
            'message' => 'Pricing updated.',
        ]);
    }

    /**
     * Select stores and create distribution entries.
     */
    public function selectStores(Request $request, Album $album): JsonResponse
    {
        $this->authorizeAccess($album);

        $validated = $request->validate([
            'store_ids'   => 'required|array',
            'store_ids.*' => 'exists:music_stores,id',
        ]);

        $songs = $album->songs;

        DB::transaction(function () use ($album, $songs, $validated) {
            // Remove existing distributions for deselected stores
            $album->distributions()
                ->whereNotIn('store_id', $validated['store_ids'])
                ->delete();

            // Create distributions for each song at each selected store
            foreach ($validated['store_ids'] as $storeId) {
                foreach ($songs as $song) {
                    $album->distributions()->firstOrCreate(
                        [
                            'song_id'  => $song->id,
                            'store_id' => $storeId,
                        ],
                        [
                            'artist_id' => $album->artist_id,
                            'status'    => 'pending',
                        ]
                    );
                }
            }
        });

        return response()->json([
            'data'    => $album->fresh()->load('distributions.store', 'stores'),
            'message' => 'Stores selected.',
        ]);
    }

    /**
     * Ensure the authenticated user owns or collaborates on this album.
     */
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

        $isOwner   = $album->artist_id === $artist->id;
        $isCollab  = $album->collaboratingArtists()
            ->where('artist_id', $artist->id)
            ->exists();

        if (! $isOwner && ! $isCollab) {
            abort(403, 'Unauthorized.');
        }
    }
}
