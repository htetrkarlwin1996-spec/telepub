<?php

namespace App\Http\Controllers;

use App\Models\MusicStore;
use App\Models\Royalty;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $artist = auth()->user()->artist;
        $filters = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:music_stores,id'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $baseQuery = Royalty::where('artist_id', $artist->id)
            ->when($filters['store_id'] ?? null, fn ($query, $id) => $query->where('store_id', $id))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->where('month', $month));

        $monthlyData = (clone $baseQuery)
            ->selectRaw('month, year, SUM(streams) as total_streams, SUM(amount) as total_revenue')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->each(fn ($row) => $row->total_revenue = $artist->getArtistShareAttribute((float) $row->total_revenue));

        $storeData = (clone $baseQuery)
            ->selectRaw('store_id, SUM(streams) as total_streams, SUM(amount) as total_revenue')
            ->with('store')
            ->groupBy('store_id')
            ->orderByDesc('total_revenue')
            ->get()
            ->each(fn ($row) => $row->total_revenue = $artist->getArtistShareAttribute((float) $row->total_revenue));

        $songPerformance = (clone $baseQuery)
            ->whereNotNull('song_id')
            ->selectRaw('song_id, SUM(streams) as total_streams, SUM(amount) as total_revenue')
            ->with('song')
            ->groupBy('song_id')
            ->orderByDesc('total_streams')
            ->take(10)
            ->get()
            ->each(fn ($row) => $row->total_revenue = $artist->getArtistShareAttribute((float) $row->total_revenue));

        $totalStreams = (int) (clone $baseQuery)->sum('streams');
        $totalRevenue = $artist->getArtistShareAttribute((float) (clone $baseQuery)->sum('amount'));
        $totalEntries = (int) (clone $baseQuery)->count();
        $stores = MusicStore::whereHas('royalties', fn ($query) => $query->where('artist_id', $artist->id))
            ->orderBy('name')->get();
        $years = Royalty::where('artist_id', $artist->id)->distinct()->orderByDesc('year')->pluck('year');

        return view('artist.analytics.index', compact(
            'monthlyData', 'storeData', 'songPerformance', 'totalStreams',
            'totalRevenue', 'totalEntries', 'stores', 'years', 'filters'
        ));
    }
}
