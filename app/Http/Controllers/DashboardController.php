<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Analytics;
use App\Models\Distribution;
use App\Models\Royalty;
use App\Models\RoyaltyAllocation;
use App\Models\Song;
use App\Models\Withdrawal;
use App\Services\WithdrawalFee;

class DashboardController extends Controller
{
    public function index(WithdrawalFee $withdrawalFee)
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail() && ! session()->has('impersonator_admin_id')) {
            return redirect()->route('verification.notice');
        }

        if ($user->isAdmin()) {
            return app(AdminController::class)->dashboard();
        }

        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }

        // Artist dashboard
        $artist = current_artist();

        if (! $artist) {
            return redirect()->route('artist.setup');
        }

        $albums = Album::where('artist_id', $artist->id)->withCount('songs')->latest()->take(5)->get();
        $songs = Song::where('artist_id', $artist->id)->latest()->take(5)->get();
        $totalSongs = Song::where('artist_id', $artist->id)->count();
        $totalAlbums = Album::where('artist_id', $artist->id)->count();
        $totalStreams = Analytics::where('artist_id', $artist->id)->sum('streams');
        $recentRoyalties = Royalty::query()
            ->join('royalty_allocations', 'royalties.id', '=', 'royalty_allocations.royalty_id')
            ->where('royalty_allocations.beneficiary_type', 'artist')
            ->where('royalty_allocations.beneficiary_id', $artist->id)
            ->select('royalties.*', 'royalty_allocations.allocated_amount as artist_amount')
            ->with('store')->latest('royalties.created_at')->take(5)->get();
        $grossByType = RoyaltyAllocation::query()
            ->join('royalties', 'royalty_allocations.royalty_id', '=', 'royalties.id')
            ->where('royalty_allocations.beneficiary_type', 'artist')
            ->where('royalty_allocations.beneficiary_id', $artist->id)
            ->selectRaw('royalties.royalty_type, COALESCE(SUM(royalty_allocations.allocated_amount), 0) as total')
            ->groupBy('royalties.royalty_type')->pluck('total', 'royalties.royalty_type');
        $balanceBreakdown = collect(Royalty::TYPES)->mapWithKeys(fn ($label, $type) => [
            $type => (float) ($grossByType[$type] ?? 0),
        ]);
        $grossRoyalties = RoyaltyAllocation::where('beneficiary_type', 'artist')
            ->where('beneficiary_id', $artist->id)->sum('gross_amount');
        $totalRoyalties = $balanceBreakdown->sum();
        $distributions = Distribution::where('artist_id', $artist->id)->with('store', 'song')->latest()->take(5)->get();
        $pendingWithdrawals = Withdrawal::where('artist_id', $artist->id)->where('status', 'pending')->sum('amount');
        $minimumWithdrawalAmount = $withdrawalFee->minimumAmount();

        return view('artist.dashboard', compact(
            'artist', 'albums', 'songs', 'totalSongs', 'totalAlbums',
            'grossRoyalties', 'totalRoyalties', 'totalStreams', 'recentRoyalties',
            'distributions', 'pendingWithdrawals', 'balanceBreakdown', 'minimumWithdrawalAmount'
        ));
    }
}
