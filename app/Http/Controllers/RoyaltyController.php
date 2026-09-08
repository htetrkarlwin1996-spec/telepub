<?php

namespace App\Http\Controllers;

use App\Models\MusicStore;
use App\Models\Royalty;
use Illuminate\Http\Request;

class RoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $artist = auth()->user()->artist;
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'store_id' => ['nullable', 'integer', 'exists:music_stores,id'],
        ]);

        $baseQuery = Royalty::where('artist_id', $artist->id)
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->where('month', $month))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId));

        $royalties = (clone $baseQuery)
            ->with(['store', 'song', 'album.collaboratingArtists'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $grossRoyalties = (float) (clone $baseQuery)->sum('amount');
        $monthlyRoyalties = (clone $baseQuery)
            ->selectRaw('month, year, SUM(amount) as total, SUM(streams) as total_streams')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $storeBreakdown = (clone $baseQuery)
            ->selectRaw('store_id, SUM(amount) as total, SUM(streams) as total_streams')
            ->with('store')
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $grossByType = (clone $baseQuery)
            ->selectRaw('royalty_type, COALESCE(SUM(amount), 0) as total')
            ->groupBy('royalty_type')
            ->pluck('total', 'royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => $artist->getArtistShareAttribute((float) ($grossByType[$type] ?? 0)),
        ]);

        $totalRoyalties = $artist->getArtistShareAttribute($grossRoyalties);
        $stores = MusicStore::whereHas('royalties', fn ($query) => $query->where('artist_id', $artist->id))
            ->orderBy('name')->get();
        $years = Royalty::where('artist_id', $artist->id)->distinct()->orderByDesc('year')->pluck('year');

        return view('artist.royalties.index', compact(
            'royalties', 'totalRoyalties', 'monthlyRoyalties', 'storeBreakdown',
            'balanceBreakdown', 'stores', 'years', 'filters'
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
