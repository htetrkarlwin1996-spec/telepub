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
            ->limit(8)
            ->get();

        $grossRoyalties = (float) (clone $baseQuery)->sum('amount');
        $monthlyRoyalties = (clone $baseQuery)
            ->selectRaw('month, year, SUM(amount) as total, SUM(streams) as total_streams')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(5)
            ->get()
            ->each(function ($row) use ($artist) {
                $row->gross_total = (float) $row->total;
                $row->total = $artist->getArtistShareAttribute($row->gross_total);
            });

        $storeBreakdown = (clone $baseQuery)
            ->selectRaw('store_id, SUM(amount) as total, SUM(streams) as total_streams')
            ->with('store')
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $albumBreakdown = (clone $baseQuery)
            ->whereNotNull('album_id')
            ->selectRaw('album_id, SUM(amount) as total, SUM(streams) as total_streams')
            ->with('album')
            ->groupBy('album_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $trackBreakdown = (clone $baseQuery)
            ->whereNotNull('song_id')
            ->selectRaw('song_id, album_id, SUM(amount) as total, SUM(streams) as total_streams')
            ->with(['song', 'album'])
            ->groupBy('song_id', 'album_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));

        $unassignedRoyalties = $artist->getArtistShareAttribute((float) (clone $baseQuery)
            ->whereNull('song_id')
            ->whereNull('album_id')
            ->sum('amount'));

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
            'royalties', 'grossRoyalties', 'totalRoyalties', 'monthlyRoyalties', 'storeBreakdown',
            'albumBreakdown', 'trackBreakdown', 'unassignedRoyalties',
            'balanceBreakdown', 'stores', 'years', 'filters'
        ));
    }

    public function all(Request $request, string $section)
    {
        abort_unless(in_array($section, ['months', 'stores', 'albums', 'tracks', 'transactions'], true), 404);

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

        $titles = [
            'months' => 'Monthly Breakdown',
            'stores' => 'Earnings by Store',
            'albums' => 'Earnings by Album',
            'tracks' => 'Earnings by Track',
            'transactions' => 'Transaction History',
        ];

        if ($section === 'months') {
            $items = (clone $baseQuery)->selectRaw('month, year, SUM(amount) as total, SUM(streams) as total_streams')
                ->groupBy('year', 'month')->orderByDesc('year')->orderByDesc('month')->paginate(25);
            $items->getCollection()->each(function ($row) use ($artist) {
                $row->gross_total = (float) $row->total;
                $row->total = $artist->getArtistShareAttribute($row->gross_total);
            });
        } elseif ($section === 'stores') {
            $items = (clone $baseQuery)->selectRaw('store_id, SUM(amount) as total, SUM(streams) as total_streams')
                ->with('store')->groupBy('store_id')->orderByDesc('total')->paginate(25);
            $items->getCollection()->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));
        } elseif ($section === 'albums') {
            $items = (clone $baseQuery)->whereNotNull('album_id')
                ->selectRaw('album_id, SUM(amount) as total, SUM(streams) as total_streams')
                ->with('album')->groupBy('album_id')->orderByDesc('total')->paginate(25);
            $items->getCollection()->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));
        } elseif ($section === 'tracks') {
            $items = (clone $baseQuery)->whereNotNull('song_id')
                ->selectRaw('song_id, album_id, SUM(amount) as total, SUM(streams) as total_streams')
                ->with(['song', 'album'])->groupBy('song_id', 'album_id')->orderByDesc('total')->paginate(25);
            $items->getCollection()->each(fn ($row) => $row->total = $artist->getArtistShareAttribute((float) $row->total));
        } else {
            $items = (clone $baseQuery)->with(['store', 'song', 'album.collaboratingArtists'])
                ->orderByDesc('year')->orderByDesc('month')->orderByDesc('id')->paginate(25);
        }

        return view('artist.royalties.all', [
            'section' => $section,
            'title' => $titles[$section],
            'items' => $items->withQueryString(),
            'artist' => $artist,
            'filters' => $filters,
        ]);
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
