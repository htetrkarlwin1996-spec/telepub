<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $artist = auth()->user()->artist;
        $withdrawals = Withdrawal::where('artist_id', $artist->id)->latest()->paginate(20);
        return view('artist.withdrawals.index', compact('withdrawals'));
    }

    public function create()
    {
        $artist = auth()->user()->artist;
        return view('artist.withdrawals.create', compact('artist'));
    }

    public function store(Request $request)
    {
        $artist = auth()->user()->artist;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $artist->available_balance,
            'payment_method' => 'required|string|max:100',
            'payment_details' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Calculate fee (e.g., 2% processing fee)
        $fee = $validated['amount'] * 0.02;
        $total = $validated['amount'] - $fee;

        Withdrawal::create([
            'artist_id' => $artist->id,
            'amount' => $validated['amount'],
            'fee' => $fee,
            'total' => $total,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_details' => $validated['payment_details'],
            'notes' => $validated['notes'],
            'requested_at' => now(),
        ]);

        // Move funds from available to pending
        $artist->decrement('available_balance', $validated['amount']);
        $artist->increment('pending_balance', $validated['amount']);

        return redirect()->route('artist.withdrawals')->with('success', 'Withdrawal request submitted for review.');
    }
}
