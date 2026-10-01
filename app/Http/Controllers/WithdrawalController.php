<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\WithdrawalFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    public function index()
    {
        $artist = current_artist();
        $withdrawals = Withdrawal::where('artist_id', $artist->id)->latest()->paginate(20);

        return view('artist.withdrawals.index', compact('withdrawals'));
    }

    public function create(WithdrawalFee $withdrawalFee)
    {
        $artist = current_artist();

        $withdrawalFeePercentage = $withdrawalFee->percentage();

        return view('artist.withdrawals.create', compact('artist', 'withdrawalFeePercentage'));
    }

    public function store(Request $request, WithdrawalFee $withdrawalFee)
    {
        $artist = current_artist();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:'.$artist->available_balance,
            'payment_method' => ['required', Rule::in(['kbz_pay', 'wave_pay', 'thai_bank_transfer', 'wire_transfer', 'paypal', 'bank_transfer', 'wise', 'payoneer'])],
            'account_name' => 'required_if:payment_method,kbz_pay,wave_pay,thai_bank_transfer|string|max:150',
            'phone' => 'required_if:payment_method,kbz_pay,wave_pay|string|max:30',
            'bank_name' => 'required_if:payment_method,thai_bank_transfer,wire_transfer|string|max:150',
            'account_number' => 'required_if:payment_method,thai_bank_transfer,wire_transfer|string|max:100',
            'branch' => 'nullable|string|max:150',
            'beneficiary_name' => 'required_if:payment_method,wire_transfer|string|max:150',
            'swift_bic' => 'required_if:payment_method,wire_transfer|string|max:30',
            'bank_address' => 'required_if:payment_method,wire_transfer|string|max:255',
            'beneficiary_address' => 'required_if:payment_method,wire_transfer|string|max:255',
            'bank_country' => 'required_if:payment_method,wire_transfer|string|max:100',
            'routing_number' => 'nullable|string|max:50',
            'payment_details' => 'required_if:payment_method,paypal,bank_transfer,wise,payoneer|nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $fields = match ($validated['payment_method']) {
            'kbz_pay', 'wave_pay' => ['account_name', 'phone'],
            'thai_bank_transfer' => ['account_name', 'bank_name', 'account_number', 'branch'],
            'wire_transfer' => ['beneficiary_name', 'bank_name', 'account_number', 'swift_bic', 'bank_address', 'beneficiary_address', 'bank_country', 'routing_number'],
            default => [],
        };
        $details = $fields
            ? json_encode(array_intersect_key($validated, array_flip($fields)))
            : ($validated['payment_details'] ?? null);

        $amounts = $withdrawalFee->calculate((float) $validated['amount']);

        DB::transaction(function () use ($artist, $validated, $amounts, $details) {
            $lockedArtist = $artist->newQuery()->lockForUpdate()->findOrFail($artist->id);
            abort_if((float) $validated['amount'] > (float) $lockedArtist->available_balance, 422, 'Insufficient balance.');
            Withdrawal::create([
                'artist_id' => $lockedArtist->id, 'amount' => $validated['amount'],
                'fee' => $amounts['fee'], 'total' => $amounts['net'], 'currency' => 'USD',
                'status' => 'pending', 'payment_method' => $validated['payment_method'],
                'payment_details' => $details, 'notes' => $validated['notes'] ?? null,
                'requested_at' => now(),
            ]);
            $lockedArtist->decrement('available_balance', $validated['amount']);
            $lockedArtist->increment('pending_balance', $validated['amount']);
        });

        return redirect()->route('artist.withdrawals')->with('success', 'Withdrawal request submitted for review.');
    }
}
