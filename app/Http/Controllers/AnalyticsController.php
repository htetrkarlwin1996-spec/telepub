<?php

namespace App\Http\Controllers;

use App\Models\Analytics;
use App\Models\Royalty;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $artist = auth()->user()->artist;

        // Monthly streams and revenue
        $monthlyData = Analytics::where('artist_id', $artist->id)
            ->selectRaw('month, year, SUM(streams) as total_streams, SUM(downloads) as total_downloads, SUM(revenue) as total_revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        // Store breakdown
        $storeData = Analytics::where('artist_id', $artist->id)
            ->selectRaw('store_id, SUM(streams) as total_streams, SUM(revenue) as total_revenue')
            ->with('store')
            ->groupBy('store_id')
            ->get();

        // Song performance
        $songPerformance = Analytics::where('artist_id', $artist->id)
            ->selectRaw('song_id, SUM(streams) as total_streams, SUM(revenue) as total_revenue')
            ->with('song')
            ->groupBy('song_id')
            ->orderBy('total_streams', 'desc')
            ->take(10)
            ->get();

        // Totals
        $totalStreams = Analytics::where('artist_id', $artist->id)->sum('streams');
        $totalRevenue = Analytics::where('artist_id', $artist->id)->sum('revenue');
        $totalDownloads = Analytics::where('artist_id', $artist->id)->sum('downloads');

        return view('artist.analytics.index', compact(
            'monthlyData', 'storeData', 'songPerformance',
            'totalStreams', 'totalRevenue', 'totalDownloads'
        ));
    }
}
