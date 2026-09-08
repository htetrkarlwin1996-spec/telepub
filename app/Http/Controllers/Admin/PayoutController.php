<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PayoutController extends Controller
{
    public function index(): Response
    {
        $payouts = PayoutRequest::query()
            ->with(['user', 'paymentMethod'])
            ->latest()
            ->paginate(15)
            ->through(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => number_format((float) $payout->amount, 2),
                    'raw_amount' => (float) $payout->amount,
                    'currency' => $payout->currency,
                    'status' => $payout->status,
                    'user_note' => $payout->user_note,
                    'admin_note' => $payout->admin_note,
                    'paid_at' => optional($payout->paid_at)->format('Y-m-d H:i'),
                    'created_at' => optional($payout->created_at)->format('Y-m-d H:i'),

                    'user' => [
                        'id' => $payout->user->id,
                        'name' => $payout->user->name,
                        'email' => $payout->user->email,
                    ],

                    'payment_method' => $payout->paymentMethod ? [
                        'type' => $payout->paymentMethod->type,
                        'type_label' => $this->typeLabel($payout->paymentMethod->type),
                        'bank_name' => $payout->paymentMethod->bank_name,
                        'account_name' => $payout->paymentMethod->account_name,
                        'account_number' => $payout->paymentMethod->account_number,
                        'phone_number' => $payout->paymentMethod->phone_number,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'status' => session('status'),
        ]);
    }

    public function approve(Request $request, PayoutRequest $payout): RedirectResponse
    {
        if ($payout->status !== 'pending') {
            return redirect()
                ->route('admin.payouts.index')
                ->withErrors(['payout' => 'Only pending payout can be approved.'])
                ->setStatusCode(303);
        }

        try {
            DB::transaction(function () use ($payout) {
                $payout->load('user.wallet');

                $wallet = $payout->user->wallet;

                if (!$wallet || (float) $wallet->balance < (float) $payout->amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }

                $wallet->balance = max(0, (float) $wallet->balance - (float) $payout->amount);
                $wallet->save();

                $payout->update([
                    'status' => 'approved',
                    'admin_note' => 'Approved by admin.',
                ]);
            });
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.payouts.index')
                ->withErrors(['payout' => $e->getMessage()])
                ->setStatusCode(303);
        }

        return redirect()
            ->route('admin.payouts.index')
            ->with('status', 'Payout approved and wallet balance deducted.')
            ->setStatusCode(303);
    }

    public function reject(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!in_array($payout->status, ['pending', 'approved'], true)) {
            return redirect()
                ->route('admin.payouts.index')
                ->withErrors(['payout' => 'This payout cannot be rejected.'])
                ->setStatusCode(303);
        }

        DB::transaction(function () use ($payout, $validated) {
            $payout->load('user.wallet');

            if ($payout->status === 'approved') {
                $wallet = $payout->user->wallet;

                if ($wallet) {
                    $wallet->balance = (float) $wallet->balance + (float) $payout->amount;
                    $wallet->save();
                }
            }

            $payout->update([
                'status' => 'rejected',
                'admin_note' => $validated['admin_note'] ?? 'Rejected by admin.',
            ]);
        });

        return redirect()
            ->route('admin.payouts.index')
            ->with('status', 'Payout rejected successfully.')
            ->setStatusCode(303);
    }

    public function markPaid(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($payout->status !== 'approved') {
            return redirect()
                ->route('admin.payouts.index')
                ->withErrors(['payout' => 'Only approved payout can be marked as paid.'])
                ->setStatusCode(303);
        }

        DB::transaction(function () use ($payout, $validated) {
            $payout->load('user.wallet');

            $wallet = $payout->user->wallet;

            if ($wallet) {
                $wallet->total_withdrawn = (float) $wallet->total_withdrawn + (float) $payout->amount;
                $wallet->save();
            }

            $payout->update([
                'status' => 'paid',
                'paid_at' => now(),
                'admin_note' => $validated['admin_note'] ?? 'Payment transferred successfully.',
            ]);
        });

        $payout->refresh()->load('user');

        Mail::send('emails.payout-paid', [
            'subjectText' => 'Your payout has been paid',
            'title' => 'Payout Paid Successfully',
            'subtitle' => 'Your payout transfer has been completed.',
            'userName' => $payout->user->name,
            'amount' => $payout->amount,
            'currency' => $payout->currency,
            'adminNote' => $payout->admin_note,
        ], function ($message) use ($payout) {
            $message->to($payout->user->email)
                ->subject('Your payout has been paid');
        });

        return redirect()
            ->route('admin.payouts.index')
            ->with('status', 'Payout marked as paid and email sent successfully.')
            ->setStatusCode(303);
    }

    private function typeLabel(?string $type): string
    {
        return match ($type) {
            'kbz_bank' => 'KBZ Bank',
            'thai_bank' => 'Thai Bank',
            'kbz_pay' => 'KBZ Pay',
            'wave_pay' => 'Wave Pay',
            default => $type ?: '-',
        };
    }
}