<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Song;
use App\Models\Distribution;
use App\Models\Royalty;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return app(AdminController::class)->dashboard();
        }

        // Artist dashboard
        $artist = $user->artist;

        if (!$artist) {
            return view('artist.setup');
        }

        $albums = Album::where('artist_id', $artist->id)->withCount('songs')->latest()->take(5)->get();
        $songs = Song::where('artist_id', $artist->id)->latest()->take(5)->get();
        $totalSongs = Song::where('artist_id', $artist->id)->count();
        $totalAlbums = Album::where('artist_id', $artist->id)->count();
        $totalStreams = \App\Models\Analytics::where('artist_id', $artist->id)->sum('streams');
        $recentRoyalties = Royalty::where('artist_id', $artist->id)->with('store')->latest()->take(5)->get();
        $grossByType = Royalty::where('artist_id', $artist->id)
            ->selectRaw('royalty_type, COALESCE(SUM(amount), 0) as total')
            ->groupBy('royalty_type')
            ->pluck('total', 'royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => $artist->getArtistShareAttribute((float) ($grossByType[$type] ?? 0)),
        ]);
        $totalRoyalties = $balanceBreakdown->sum();
        $distributions = Distribution::where('artist_id', $artist->id)->with('store', 'song')->latest()->take(5)->get();
        $pendingWithdrawals = Withdrawal::where('artist_id', $artist->id)->where('status', 'pending')->sum('amount');

        return view('artist.dashboard', compact(
            'artist', 'albums', 'songs', 'totalSongs', 'totalAlbums',
            'totalRoyalties', 'totalStreams', 'recentRoyalties',
            'distributions', 'pendingWithdrawals', 'balanceBreakdown'
        ));
    }
}
