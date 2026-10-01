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

        $royalties = Royalty::query()
            ->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')
            ->where('royalty_allocations.beneficiary_id', $artist->id)
            ->select('royalties.*', 'royalty_allocations.allocated_amount as artist_amount')
            ->with('store', 'album', 'song')
            ->orderBy('royalties.year', 'desc')
            ->orderBy('royalties.month', 'desc')
            ->orderBy('royalties.id', 'desc')
            ->paginate($request->get('per_page', 20));

        $items = collect($royalties->items())->map(function (Royalty $royalty) use ($artist) {
            $data = $royalty->toArray();
            $data['amount'] = $royalty->amountForArtist($artist);
            $data['earnings'] = $data['amount'];

            return $data;
        });

        return response()->json([
            'data' => $items,
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

        $baseQuery = Royalty::query()
            ->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')
            ->where('royalty_allocations.beneficiary_id', $artist->id);
        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(royalty_allocations.allocated_amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(royalties.streams), 0) as total_streams')
            ->first();

        $monthly = (clone $baseQuery)
            ->where('royalties.year', $year)
            ->selectRaw('royalties.month, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as amount, COALESCE(SUM(royalties.streams), 0) as streams')
            ->groupBy('royalties.month')->orderBy('royalties.month')
            ->get();

        $byStore = (clone $baseQuery)
            ->where('royalties.year', $year)
            ->selectRaw('royalties.store_id, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as amount, COALESCE(SUM(royalties.streams), 0) as streams')
            ->with('store')
            ->groupBy('royalties.store_id')
            ->get();

        $grossByType = (clone $baseQuery)
            ->selectRaw('royalties.royalty_type, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as amount')
            ->groupBy('royalties.royalty_type')->pluck('amount', 'royalties.royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => round((float) ($grossByType[$type] ?? 0), 2),
        ]);
        $totalEarnings = (float) $totals->total_amount;

        return response()->json([
            'data' => [
                'total_amount' => $totalEarnings,
                'total_earnings' => $totalEarnings,
                'total_streams' => (int) $totals->total_streams,
                'balance_breakdown' => [
                    ...$balanceBreakdown->all(),
                    'total_balance' => round($balanceBreakdown->sum(), 2),
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

        if (! $user->isAdmin() && ! $royalty->allocations()->where('beneficiary_type', 'artist')->where('beneficiary_id', $user?->artist?->id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $royalty->load('store', 'album', 'song', 'artist', 'allocations');

        if ($user->isAdmin()) {
            return response()->json(['data' => $royalty]);
        }

        $data = $royalty->toArray();
        $data['amount'] = $royalty->amountForArtist($user->artist);
        $data['earnings'] = $data['amount'];
        unset($data['artist']);

        return response()->json(['data' => $data]);
    }
}
