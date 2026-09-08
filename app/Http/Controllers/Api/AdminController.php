<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Payout;
use App\Models\Royalty;
use App\Models\Song;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\RoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ===== DASHBOARD =====

    public function dashboard(): JsonResponse
    {
        $royalties = Royalty::with('artist')->get();
        $totalRoyalties = (float) $royalties->sum('amount');
        $totalArtistShares = (float) $royalties->sum(
            fn (Royalty $royalty) => $royalty->artist?->getArtistShareAttribute($royalty->amount) ?? $royalty->amount
        );

        $stats = [
            'total_artists'   => Artist::count(),
            'total_albums'    => Album::count(),
            'total_songs'     => Song::count(),
            'total_royalties' => $totalRoyalties,
            'total_artist_shares' => $totalArtistShares,
            'total_telemusic_fees' => round($totalRoyalties - $totalArtistShares, 2),
            'total_paid' => (float) Payout::where('status', 'paid')->sum('amount'),
            'total_releases'  => Album::where('status', 'approved')->count(),
            'pending_releases' => Album::where('status', 'submitted')->count(),
            'pending_withdrawals' => (float) Withdrawal::where('status', 'pending')->sum('amount'),
            'recent_artists'  => Artist::with('user')->latest()->take(5)->get(),
            'recent_releases' => Album::with('artist')->latest()->take(5)->get(),
            'recent_withdrawals' => Withdrawal::with('artist')->latest()->take(5)->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    // ===== ARTISTS =====

    public function artists(): JsonResponse
    {
        $artists = Artist::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', 20));

        return response()->json([
            'data' => $artists->items(),
            'meta' => [
                'current_page' => $artists->currentPage(),
                'last_page'    => $artists->lastPage(),
                'per_page'     => $artists->perPage(),
                'total'        => $artists->total(),
            ],
        ]);
    }

    public function storeArtist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'artist_name'           => 'required|string|max:255',
            'genre'                 => 'nullable|string|max:255',
            'country'               => 'nullable|string|max:100',
            'revenue_share_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'artist',
            'is_active' => true,
        ]);

        $artist = $user->artist()->create([
            'artist_name'              => $validated['artist_name'],
            'genre'                    => $validated['genre'] ?? null,
            'country'                  => $validated['country'] ?? null,
            'revenue_share_percentage' => $validated['revenue_share_percentage'] ?? 70,
        ]);

        return response()->json([
            'data'    => $artist->load('user'),
            'message' => 'Artist created.',
        ], 201);
    }

    public function updateArtist(Request $request, Artist $artist): JsonResponse
    {
        $validated = $request->validate([
            'artist_name'              => 'sometimes|string|max:255',
            'genre'                    => 'nullable|string|max:255',
            'country'                  => 'nullable|string|max:100',
            'revenue_share_percentage' => 'nullable|numeric|min:0|max:100',
            'bio'                      => 'nullable|string',
            'avatar'                   => 'nullable|string',
        ]);

        $artist->update($validated);

        return response()->json([
            'data'    => $artist->fresh()->load('user'),
            'message' => 'Artist updated.',
        ]);
    }

    // ===== RELEASES =====

    public function releases(): JsonResponse
    {
        $albums = Album::with('artist', 'songs', 'stores', 'collaboratingArtists')
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', 20));

        return response()->json([
            'data' => $albums->items(),
            'meta' => [
                'current_page' => $albums->currentPage(),
                'last_page'    => $albums->lastPage(),
                'per_page'     => $albums->perPage(),
                'total'        => $albums->total(),
            ],
        ]);
    }

    public function showRelease(Album $album): JsonResponse
    {
        return response()->json([
            'data' => $album->load('artist', 'songs', 'stores', 'collaboratingArtists', 'distributions.store'),
        ]);
    }

    public function approveRelease(Request $request, Album $album): JsonResponse
    {
        $album->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'notes'       => $request->input('notes', $album->notes),
        ]);

        return response()->json([
            'data'    => $album->fresh()->load('artist'),
            'message' => 'Release approved.',
        ]);
    }

    public function rejectRelease(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $album->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'data'    => $album->fresh()->load('artist'),
            'message' => 'Release rejected.',
        ]);
    }

    // ===== ROYALTIES =====

    public function royalties(): JsonResponse
    {
        $royalties = Royalty::with('artist', 'store', 'album', 'song')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(request('per_page', 20));

        return response()->json([
            'data' => $royalties->items(),
            'meta' => [
                'current_page' => $royalties->currentPage(),
                'last_page'    => $royalties->lastPage(),
                'per_page'     => $royalties->perPage(),
                'total'        => $royalties->total(),
            ],
        ]);
    }

    public function storeRoyalty(Request $request, RoyaltyService $royaltyService): JsonResponse
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'song_id'   => 'nullable|exists:songs,id',
            'album_id'  => 'nullable|exists:albums,id',
            'store_id'  => 'required|exists:music_stores,id',
            'royalty_type' => 'required|in:' . implode(',', array_keys(Royalty::TYPES)),
            'month'     => 'required|integer|min:1|max:12',
            'year'      => 'required|integer|min:2020',
            'amount'    => 'required|numeric|min:0',
            'streams'   => 'nullable|integer|min:0',
            'currency'  => 'nullable|string|size:3',
            'notes'     => 'nullable|string|max:1000',
        ]);

        $royalty = $royaltyService->create([
            ...$validated,
            'entered_by' => request()->user()->id,
            'currency'   => $validated['currency'] ?? 'USD',
        ]);

        return response()->json([
            'data'    => $royalty->load('artist', 'store'),
            'message' => 'Royalty added.',
        ], 201);
    }

    public function updateRoyalty(Request $request, Royalty $royalty, RoyaltyService $royaltyService): JsonResponse
    {
        $validated = $request->validate([
            'amount'  => 'sometimes|numeric|min:0',
            'royalty_type' => 'sometimes|in:' . implode(',', array_keys(Royalty::TYPES)),
            'artist_id' => 'sometimes|exists:artists,id',
            'streams' => 'nullable|integer|min:0',
            'month'   => 'sometimes|integer|min:1|max:12',
            'year'    => 'sometimes|integer|min:2020',
            'notes'   => 'nullable|string|max:1000',
        ]);

        $royalty = $royaltyService->update($royalty, $validated);

        return response()->json([
            'data'    => $royalty->fresh()->load('artist', 'store'),
            'message' => 'Royalty updated.',
        ]);
    }

    // ===== WITHDRAWALS =====

    public function withdrawals(): JsonResponse
    {
        $withdrawals = Withdrawal::with('artist')
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', 20));

        return response()->json([
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
                'per_page'     => $withdrawals->perPage(),
                'total'        => $withdrawals->total(),
            ],
        ]);
    }

    public function approveWithdrawal(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal->update([
            'status'       => 'approved',
            'processed_at' => now(),
        ]);

        return response()->json([
            'data'    => $withdrawal->fresh()->load('artist'),
            'message' => 'Withdrawal approved.',
        ]);
    }

    public function completeWithdrawal(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal->update([
            'status'       => 'completed',
            'processed_at' => now(),
        ]);

        return response()->json([
            'data'    => $withdrawal->fresh()->load('artist'),
            'message' => 'Withdrawal completed.',
        ]);
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $withdrawal->update([
            'status'      => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? 'Rejected by admin.',
        ]);

        return response()->json([
            'data'    => $withdrawal->fresh()->load('artist'),
            'message' => 'Withdrawal rejected.',
        ]);
    }

    // ===== STORES =====

    public function stores(): JsonResponse
    {
        $stores = MusicStore::orderBy('name')->get();

        return response()->json(['data' => $stores]);
    }

    public function storeStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:music_stores,slug',
            'logo'        => 'nullable|string',
            'description' => 'nullable|string',
            'url'         => 'nullable|url|max:500',
            'is_active'   => 'boolean',
        ]);

        $store = MusicStore::create($validated);

        return response()->json([
            'data'    => $store,
            'message' => 'Store created.',
        ], 201);
    }

    public function updateStore(Request $request, MusicStore $store): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'slug'        => 'sometimes|string|max:255|unique:music_stores,slug,'.$store->id,
            'logo'        => 'nullable|string',
            'description' => 'nullable|string',
            'url'         => 'nullable|url|max:500',
            'is_active'   => 'boolean',
        ]);

        $store->update($validated);

        return response()->json([
            'data'    => $store->fresh(),
            'message' => 'Store updated.',
        ]);
    }
}
