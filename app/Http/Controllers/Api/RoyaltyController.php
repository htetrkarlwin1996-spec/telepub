<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Royalty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoyaltyController extends Controller
{
    /**
     * List royalties for the authenticated artist (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $royalties = Royalty::where('artist_id', $artist->id)
            ->with('store', 'album', 'song')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $royalties->items(),
            'meta' => [
                'current_page' => $royalties->currentPage(),
                'last_page' => $royalties->lastPage(),
                'per_page' => $royalties->perPage(),
                'total' => $royalties->total(),
            ],
        ]);
    }

    /**
     * Royalty summary: total earnings, monthly breakdown.
     */
    public function summary(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $year = $request->get('year', date('Y'));

        $totals = Royalty::where('artist_id', $artist->id)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(streams), 0) as total_streams')
            ->first();

        $monthly = Royalty::where('artist_id', $artist->id)
            ->where('year', $year)
            ->selectRaw('month, COALESCE(SUM(amount), 0) as amount, COALESCE(SUM(streams), 0) as streams')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $byStore = Royalty::where('artist_id', $artist->id)
            ->where('year', $year)
            ->selectRaw('store_id, COALESCE(SUM(amount), 0) as amount, COALESCE(SUM(streams), 0) as streams')
            ->with('store')
            ->groupBy('store_id')
            ->get();

        $grossByType = Royalty::where('artist_id', $artist->id)
            ->selectRaw('royalty_type, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('royalty_type')
            ->pluck('amount', 'royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => round($artist->getArtistShareAttribute((float) ($grossByType[$type] ?? 0)), 2),
        ]);

        return response()->json([
            'data' => [
                'total_amount' => (float) $totals->total_amount,
                'total_streams' => (int) $totals->total_streams,
                'artist_share' => $artist->getArtistShareAttribute($totals->total_amount),
                'tele_music_fee' => $artist->getTeleMusicFeeAttribute($totals->total_amount),
                'balance_breakdown' => [
                    ...$balanceBreakdown->all(),
                    'total_balance' => round($balanceBreakdown->sum(), 2),
                    'gross_amount' => (float) $totals->total_amount,
                    'revenue_share_percentage' => (float) $artist->revenue_share_percentage,
                    'artist_share_amount' => $artist->getArtistShareAttribute($totals->total_amount),
                    'telemusic_fee_percentage' => (float) $artist->tele_music_fee_percentage,
                    'telemusic_fee_amount' => $artist->getTeleMusicFeeAttribute($totals->total_amount),
                    'currency' => 'USD',
                ],
                'monthly' => $monthly,
                'by_store' => $byStore,
            ],
        ]);
    }

    /**
     * Single royalty detail.
     */
    public function show(Royalty $royalty): JsonResponse
    {
        $user = request()->user();

        if (! $user->isAdmin() && $royalty->artist_id !== $user?->artist?->id) {
            abort(403, 'Unauthorized.');
        }

        return response()->json([
            'data' => $royalty->load('store', 'album', 'song', 'artist'),
        ]);
    }
}
