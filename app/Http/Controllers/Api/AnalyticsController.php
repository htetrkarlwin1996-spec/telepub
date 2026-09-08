<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Get analytics data for the authenticated artist.
     */
    public function index(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        $query = Analytics::where('artist_id', $artist->id)->where('year', $year);

        if ($month) {
            $query->where('month', $month);
        }

        $analytics = $query->with('song', 'album', 'store')->orderBy('month', 'desc')->get();

        $totals = [
            'total_streams'    => (int) $analytics->sum('streams'),
            'total_downloads'  => (int) $analytics->sum('downloads'),
            'total_likes'      => (int) $analytics->sum('likes'),
            'total_playlist_adds' => (int) $analytics->sum('playlist_adds'),
            'total_revenue'    => (float) $analytics->sum('revenue'),
        ];

        return response()->json([
            'data' => [
                'analytics' => $analytics,
                'totals'    => $totals,
            ],
        ]);
    }
}
