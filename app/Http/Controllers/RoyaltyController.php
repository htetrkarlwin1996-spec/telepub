<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\Royalty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $artist = current_artist();
        $filters = $this->filters($request);
        $baseQuery = $this->allocationQuery($artist, $filters);
        $royalties = (clone $baseQuery)->select('royalties.*', 'royalty_allocations.allocated_amount as artist_amount')
            ->with(['store', 'song', 'album.collaboratingArtists'])
            ->orderByDesc('royalties.year')->orderByDesc('royalties.month')->orderByDesc('royalties.id')->limit(8)->get();
        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(royalties.amount), 0) as gross_total, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as artist_total')->first();
        $grossRoyalties = (float) $totals->gross_total;
        $totalRoyalties = (float) $totals->artist_total;
        $monthlyRoyalties = (clone $baseQuery)
            ->selectRaw('royalties.month, royalties.year, SUM(royalties.amount) as gross_total, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
            ->groupBy('royalties.year', 'royalties.month')->orderByDesc('royalties.year')->orderByDesc('royalties.month')->limit(5)->get();
        $storeBreakdown = (clone $baseQuery)
            ->selectRaw('royalties.store_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
            ->with('store')->groupBy('royalties.store_id')->orderByDesc('total')->limit(5)->get();
        $albumBreakdown = (clone $baseQuery)->whereNotNull('royalties.album_id')
            ->selectRaw('royalties.album_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
            ->with('album')->groupBy('royalties.album_id')->orderByDesc('total')->limit(5)->get();
        $trackBreakdown = (clone $baseQuery)->whereNotNull('royalties.song_id')
            ->selectRaw('royalties.song_id, royalties.album_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
            ->with(['song', 'album'])->groupBy('royalties.song_id', 'royalties.album_id')->orderByDesc('total')->limit(5)->get();
        $unassignedRoyalties = (float) (clone $baseQuery)->whereNull('royalties.song_id')->whereNull('royalties.album_id')
            ->sum('royalty_allocations.allocated_amount');
        $allocatedByType = (clone $baseQuery)
            ->selectRaw('royalties.royalty_type, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as total')
            ->groupBy('royalties.royalty_type')->pluck('total', 'royalties.royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [$type => (float) ($allocatedByType[$type] ?? 0)]);
        $storeIds = (clone $baseQuery)->distinct()->pluck('royalties.store_id')->filter();
        $stores = MusicStore::whereIn('id', $storeIds)->orderBy('name')->get();
        $years = $this->allocationQuery($artist, [])->distinct()->orderByDesc('royalties.year')->pluck('royalties.year');

        return view('artist.royalties.index', compact(
            'royalties', 'grossRoyalties', 'totalRoyalties', 'monthlyRoyalties', 'storeBreakdown',
            'albumBreakdown', 'trackBreakdown', 'unassignedRoyalties', 'balanceBreakdown', 'stores', 'years', 'filters'
        ));
    }

    public function all(Request $request, string $section)
    {
        abort_unless(in_array($section, ['months', 'stores', 'albums', 'tracks', 'transactions'], true), 404);
        $artist = current_artist();
        $filters = $this->filters($request);
        $baseQuery = $this->allocationQuery($artist, $filters);
        $titles = ['months' => 'Monthly Breakdown', 'stores' => 'Earnings by Store', 'albums' => 'Earnings by Album',
            'tracks' => 'Earnings by Track', 'transactions' => 'Transaction History'];

        if ($section === 'months') {
            $items = (clone $baseQuery)
                ->selectRaw('royalties.month, royalties.year, SUM(royalties.amount) as gross_total, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
                ->groupBy('royalties.year', 'royalties.month')->orderByDesc('royalties.year')->orderByDesc('royalties.month')->paginate(25);
        } elseif ($section === 'stores') {
            $items = (clone $baseQuery)->selectRaw('royalties.store_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
                ->with('store')->groupBy('royalties.store_id')->orderByDesc('total')->paginate(25);
        } elseif ($section === 'albums') {
            $items = (clone $baseQuery)->whereNotNull('royalties.album_id')
                ->selectRaw('royalties.album_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
                ->with('album')->groupBy('royalties.album_id')->orderByDesc('total')->paginate(25);
        } elseif ($section === 'tracks') {
            $items = (clone $baseQuery)->whereNotNull('royalties.song_id')
                ->selectRaw('royalties.song_id, royalties.album_id, SUM(royalty_allocations.allocated_amount) as total, SUM(royalties.streams) as total_streams')
                ->with(['song', 'album'])->groupBy('royalties.song_id', 'royalties.album_id')->orderByDesc('total')->paginate(25);
        } else {
            $items = (clone $baseQuery)->select('royalties.*', 'royalty_allocations.allocated_amount as artist_amount')
                ->with(['store', 'song', 'album.collaboratingArtists'])
                ->orderByDesc('royalties.year')->orderByDesc('royalties.month')->orderByDesc('royalties.id')->paginate(25);
        }

        return view('artist.royalties.all', [
            'section' => $section, 'title' => $titles[$section], 'items' => $items->withQueryString(),
            'artist' => $artist, 'filters' => $filters,
        ]);
    }

    public function show(Royalty $royalty)
    {
        $artist = current_artist();
        abort_unless($royalty->allocations()->where('beneficiary_type', 'artist')->where('beneficiary_id', $artist->id)->exists(), 403);
        $royalty->load(['store', 'song', 'album', 'allocations']);

        return view('artist.royalties.show', compact('royalty', 'artist'));
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'year' => ['nullable', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'store_id' => ['nullable', 'integer', 'exists:music_stores,id'],
        ]);
    }

    private function allocationQuery(Artist $artist, array $filters): Builder
    {
        return Royalty::query()->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')->where('royalty_allocations.beneficiary_id', $artist->id)
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('royalties.year', $year))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->where('royalties.month', $month))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->where('royalties.store_id', $storeId));
    }
}
