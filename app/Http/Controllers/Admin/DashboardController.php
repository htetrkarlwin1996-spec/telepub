<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\PayoutRequest;
use App\Models\TakedownRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Business Capital
        |--------------------------------------------------------------------------
        | Initial business investment / base capital
        */
        $initialCapital = 27000000;

        /*
        |--------------------------------------------------------------------------
        | Revenue / Amount Calculations
        |--------------------------------------------------------------------------
        */
        $totalRegistrationAmount = (float) Album::sum('registration_fee');

        $paidPayoutAmount = (float) PayoutRequest::where('status', 'paid')
            ->sum('amount');

        $pendingPayoutAmount = (float) PayoutRequest::whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Net Position
        |--------------------------------------------------------------------------
        | Initial Capital + Registration Fees - Paid Payouts
        */
        $netPosition = $initialCapital + $totalRegistrationAmount - $paidPayoutAmount;

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                /*
                |--------------------------------------------------------------------------
                | Count Stats
                |--------------------------------------------------------------------------
                */
                'users_count' => User::where('role', 'user')->count(),

                'albums_count' => Album::count(),
                'pending_albums_count' => Album::where('status', 'pending')->count(),
                'completed_albums_count' => Album::where('status', 'completed')->count(),
                'delivery_albums_count' => Album::where('status', 'delivery')->count(),

                'pending_payouts_count' => PayoutRequest::where('status', 'pending')->count(),
                'approved_payouts_count' => PayoutRequest::where('status', 'approved')->count(),
                'paid_payouts_count' => PayoutRequest::where('status', 'paid')->count(),

                'pending_takedowns_count' => TakedownRequest::where('status', 'pending')->count(),

                /*
                |--------------------------------------------------------------------------
                | Money Stats
                |--------------------------------------------------------------------------
                */
                'currency' => 'MMK',

                'initial_capital' => number_format($initialCapital, 2),
                'total_registration_amount' => number_format($totalRegistrationAmount, 2),
                'paid_payout_amount' => number_format($paidPayoutAmount, 2),
                'pending_payout_amount' => number_format($pendingPayoutAmount, 2),
                'net_position' => number_format($netPosition, 2),

                /*
                |--------------------------------------------------------------------------
                | Raw Money Stats
                |--------------------------------------------------------------------------
                | Later chart / calculation အတွက် အသုံးပြုနိုင်အောင် raw value ပါထည့်ထားတယ်
                */
                'raw_initial_capital' => $initialCapital,
                'raw_total_registration_amount' => $totalRegistrationAmount,
                'raw_paid_payout_amount' => $paidPayoutAmount,
                'raw_pending_payout_amount' => $pendingPayoutAmount,
                'raw_net_position' => $netPosition,
            ],
        ]);
    }
}