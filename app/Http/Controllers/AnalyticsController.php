<?php

namespace App\Http\Controllers;

use App\Models\MusicStore;
use App\Models\Royalty;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $artist = current_artist();
        $filters = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:music_stores,id'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $baseQuery = Royalty::query()
            ->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')
            ->where('royalty_allocations.beneficiary_id', $artist->id)
            ->when($filters['store_id'] ?? null, fn ($query, $id) => $query->where('royalties.store_id', $id))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('royalties.year', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->where('royalties.month', $month));

        $monthlyData = (clone $baseQuery)
            ->selectRaw('royalties.month, royalties.year, SUM(royalties.streams) as total_streams, SUM(royalty_allocations.allocated_amount) as total_revenue')
            ->groupBy('royalties.year', 'royalties.month')
            ->orderByDesc('royalties.year')->orderByDesc('royalties.month')->get();

        $storeData = (clone $baseQuery)
            ->selectRaw('royalties.store_id, SUM(royalties.streams) as total_streams, SUM(royalty_allocations.allocated_amount) as total_revenue')
            ->with('store')
            ->groupBy('royalties.store_id')->orderByDesc('total_revenue')->get();

        $songPerformance = (clone $baseQuery)
            ->whereNotNull('royalties.song_id')
            ->selectRaw('royalties.song_id, SUM(royalties.streams) as total_streams, SUM(royalty_allocations.allocated_amount) as total_revenue')
            ->with('song')
            ->groupBy('royalties.song_id')
            ->orderByDesc('total_streams')
            ->take(10)->get();

        $totalStreams = (int) (clone $baseQuery)->sum('royalties.streams');
        $totalRevenue = (float) (clone $baseQuery)->sum('royalty_allocations.allocated_amount');
        $totalEntries = (int) (clone $baseQuery)->count('royalties.id');
        $stores = MusicStore::whereIn('id', (clone $baseQuery)->distinct()->pluck('royalties.store_id'))->orderBy('name')->get();
        $years = Royalty::query()->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')->where('royalty_allocations.beneficiary_id', $artist->id)
            ->distinct()->orderByDesc('royalties.year')->pluck('royalties.year');

        return view('artist.analytics.index', compact(
            'monthlyData', 'storeData', 'songPerformance', 'totalStreams',
            'totalRevenue', 'totalEntries', 'stores', 'years', 'filters'
        ));
    }
}
