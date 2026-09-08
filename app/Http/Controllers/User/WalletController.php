<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
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

        $paymentMethods = $user->paymentMethods()
            ->latest()
            ->get()
            ->map(function ($method) {
                return [
                    'id' => $method->id,
                    'type' => $method->type,
                    'type_label' => $this->typeLabel($method->type),
                    'bank_name' => $method->bank_name,
                    'account_name' => $method->account_name,
                    'account_number' => $method->account_number,
                    'phone_number' => $method->phone_number,
                    'is_default' => (bool) $method->is_default,
                ];
            });

        $payoutRequests = $user->payoutRequests()
            ->with('paymentMethod')
            ->latest()
            ->get()
            ->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => number_format((float) $payout->amount, 2),
                    'currency' => $payout->currency,
                    'status' => $payout->status,
                    'user_note' => $payout->user_note,
                    'admin_note' => $payout->admin_note,
                    'paid_at' => optional($payout->paid_at)->format('Y-m-d H:i'),
                    'created_at' => optional($payout->created_at)->format('Y-m-d H:i'),
                    'payment_method' => $payout->paymentMethod ? [
                        'type_label' => $this->typeLabel($payout->paymentMethod->type),
                        'bank_name' => $payout->paymentMethod->bank_name,
                        'account_name' => $payout->paymentMethod->account_name,
                        'account_number' => $payout->paymentMethod->account_number,
                        'phone_number' => $payout->paymentMethod->phone_number,
                    ] : null,
                ];
            });

        return Inertia::render('User/Wallet/Index', [
            'wallet' => [
                'balance' => number_format((float) $wallet->balance, 2),
                'raw_balance' => (float) $wallet->balance,
                'total_earned' => number_format((float) $wallet->total_earned, 2),
                'total_withdrawn' => number_format((float) $wallet->total_withdrawn, 2),
                'currency' => $wallet->currency,
            ],
            'paymentMethods' => $paymentMethods,
            'payoutRequests' => $payoutRequests,
            'status' => session('status'),
        ]);
    }

    public function storePayoutRequest(Request $request): RedirectResponse
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

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $paymentMethod = $user->paymentMethods()
            ->where('id', $validated['payment_method_id'])
            ->first();

        if (!$paymentMethod) {
            return back()->withErrors([
                'payment_method_id' => 'Invalid payment method selected.',
            ]);
        }

        if ((float) $validated['amount'] > (float) $wallet->balance) {
            return back()->withErrors([
                'amount' => 'Insufficient wallet balance.',
            ]);
        }

        PayoutRequest::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $validated['amount'],
            'currency' => $wallet->currency,
            'status' => 'pending',
            'user_note' => $validated['user_note'] ?? null,
        ]);

        
        $adminEmail = config('mail.admin_email');

        if ($adminEmail) {
            Mail::raw(
                "New payout request received.\n\n"
                . "User: {$user->name}\n"
                . "Email: {$user->email}\n"
                . "Amount: {$validated['amount']} {$wallet->currency}\n"
                . "Payment Method: {$this->typeLabel($paymentMethod->type)}\n"
                . "Account Name: {$paymentMethod->account_name}\n",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('New Payout Request');
                }
            );
        }

        return back()->with('status', 'Payout request submitted successfully.');
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'kbz_bank' => 'KBZ Bank',
            'thai_bank' => 'Thai Bank',
            'kbz_pay' => 'KBZ Pay',
            'wave_pay' => 'Wave Pay',
            default => $type,
        };
    }
}