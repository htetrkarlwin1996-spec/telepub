<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    /**
     * List withdrawals for the authenticated artist.
     */
    public function index(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $withdrawals = Withdrawal::where('artist_id', $artist->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
                'per_page'     => $withdrawals->perPage(),
                'total'        => $withdrawals->total(),
            ],
        ]);
    }

    /**
     * Request a new withdrawal.
     */
    public function store(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'Artist profile required.'], 404);
        }

        $validated = $request->validate([
            'amount'          => 'required|numeric|min:1',
            'payment_method'  => 'required|string|in:paypal,bank_transfer,kbz_pay,wave_pay',
            'payment_details' => 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ]);

        // Check balance
        if ($validated['amount'] > $artist->available_balance) {
            return response()->json([
                'message' => 'Insufficient balance.',
                'data'    => [
                    'available_balance' => (float) $artist->available_balance,
                    'requested_amount'  => (float) $validated['amount'],
                ],
            ], 422);
        }

        $fee = 0; // Could calculate fee logic here
        $total = $validated['amount'] + $fee;

        $withdrawal = Withdrawal::create([
            'artist_id'       => $artist->id,
            'amount'          => $validated['amount'],
            'fee'             => $fee,
            'total'           => $total,
            'currency'        => 'USD',
            'status'          => 'pending',
            'payment_method'  => $validated['payment_method'],
            'payment_details' => $validated['payment_details'],
            'notes'           => $validated['notes'],
        ]);

        return response()->json([
            'data'    => $withdrawal,
            'message' => 'Withdrawal requested.',
        ], 201);
    }
}
