<?php

namespace App\Http\Controllers;

use App\Models\Royalty;
use Illuminate\Http\Request;

class RoyaltyController extends Controller
{
    public function index()
    {
        $artist = auth()->user()->artist;
        
        $royalties = Royalty::where('artist_id', $artist->id)
            ->with(['store', 'song', 'album.collaboratingArtists'])
            ->latest()
            ->paginate(20);

        $grossRoyalties = (float) Royalty::where('artist_id', $artist->id)->sum('amount');
        
        $monthlyRoyalties = Royalty::where('artist_id', $artist->id)
            ->selectRaw('month, year, SUM(amount) as total, SUM(streams) as total_streams')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $storeBreakdown = Royalty::where('artist_id', $artist->id)
            ->selectRaw('store_id, SUM(amount) as total')
            ->with('store')
            ->groupBy('store_id')
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $grossByType = Royalty::where('artist_id', $artist->id)
            ->selectRaw('royalty_type, COALESCE(SUM(amount), 0) as total')
            ->groupBy('royalty_type')
            ->pluck('total', 'royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => $artist->getArtistShareAttribute((float) ($grossByType[$type] ?? 0)),
        ]);

        $totalRoyalties = $artist->getArtistShareAttribute($grossRoyalties);

        return view('artist.royalties.index', compact(
            'royalties', 'totalRoyalties', 'monthlyRoyalties', 'storeBreakdown', 'balanceBreakdown'
        ));
    }

    public function show(Royalty $royalty)
    {
        $artist = auth()->user()->artist;
        if ($royalty->artist_id !== $artist->id) {
            abort(403);
        }
        return view('artist.royalties.show', compact('royalty'));
    }
}
