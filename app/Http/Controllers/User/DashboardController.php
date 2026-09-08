<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'currency' => 'MMK',
            ]
        );

        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
            ]
        );

        $agreement = $user->agreement;

        return Inertia::render('Dashboard', [
            'stats' => [
                'wallet_balance' => number_format((float) $wallet->balance, 2),
                'currency' => $wallet->currency,
                'albums_count' => $user->albums()->count(),
                'songs_count' => $user->songs()->count(),
                'pending_payouts_count' => $user->payoutRequests()->where('status', 'pending')->count(),
                'takedown_requests_count' => $user->takedownRequests()->count(),
            ],

            'profile' => [
                'display_name' => $profile->display_name,
                'artist_name' => $profile->artist_name,
                'publisher_name' => $profile->publisher_name,
                'ipi_name' => $profile->ipi_name,
                'ipi_number' => $profile->ipi_number,
                'country' => $profile->country,
                'city' => $profile->city,
                'address' => $profile->address,
            ],

            'agreement' => $agreement ? [
                'agreement_name' => $agreement->agreement_name,
                'signed_name' => $agreement->signed_name,
                'signed_date' => optional($agreement->signed_date)->format('Y-m-d'),
                'url' => asset('storage/' . $agreement->pdf_path),
            ] : null,
        ]);
    }
}