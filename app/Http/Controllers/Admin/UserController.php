<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $users = User::query()
            ->where('role', 'user')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with(['wallet', 'profile'])
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => (bool) $user->is_active,
                    'email_verified_at' => optional($user->email_verified_at)->format('Y-m-d H:i'),
                    'created_at' => optional($user->created_at)->format('Y-m-d'),
                    'wallet_balance' => number_format((float) optional($user->wallet)->balance, 2),
                    'currency' => optional($user->wallet)->currency ?? 'MMK',
                    'artist_name' => optional($user->profile)->artist_name,
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
            'status' => session('status'),
        ]);
    }

    public function show(User $user, UserSetupService $userSetupService): Response
    {
        abort_unless($user->role === 'user', 404);

        $userSetupService->createDefaults($user);

        $user->load([
            'profile',
            'wallet',
            'agreement',
            'paymentMethods',
            'albums.songs',
            'payoutRequests.paymentMethod',
            'takedownRequests',
        ]);

        return Inertia::render('Admin/Users/Show', [
            'selectedUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool) $user->is_active,
                'email_verified_at' => optional($user->email_verified_at)->format('Y-m-d H:i'),
                'created_at' => optional($user->created_at)->format('Y-m-d H:i'),
            ],

            'profile' => $user->profile ? [
                'display_name' => $user->profile->display_name,
                'artist_name' => $user->profile->artist_name,
                'publisher_name' => $user->profile->publisher_name,
                'ipi_name' => $user->profile->ipi_name,
                'ipi_number' => $user->profile->ipi_number,
                'country' => $user->profile->country,
                'city' => $user->profile->city,
                'address' => $user->profile->address,
            ] : null,

            'wallet' => [
                'balance' => number_format((float) $user->wallet->balance, 2),
                'raw_balance' => (float) $user->wallet->balance,
                'total_earned' => number_format((float) $user->wallet->total_earned, 2),
                'total_withdrawn' => number_format((float) $user->wallet->total_withdrawn, 2),
                'currency' => $user->wallet->currency,
            ],

            'agreement' => $user->agreement ? [
                'agreement_name' => $user->agreement->agreement_name,
                'signed_name' => $user->agreement->signed_name,
                'signed_date' => optional($user->agreement->signed_date)->format('Y-m-d'),
                'url' => asset('storage/' . $user->agreement->pdf_path),
            ] : null,

            'paymentMethods' => $user->paymentMethods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'type' => $method->type,
                    'type_label' => $this->paymentTypeLabel($method->type),
                    'bank_name' => $method->bank_name,
                    'account_name' => $method->account_name,
                    'account_number' => $method->account_number,
                    'phone_number' => $method->phone_number,
                    'is_default' => (bool) $method->is_default,
                ];
            }),

            'albums' => $user->albums->map(function ($album) {
                return [
                    'id' => $album->id,
                    'album_name' => $album->album_name,
                    'artist_name' => $album->artist_name,
                    'status' => $album->status,
                    'songs_count' => $album->songs->count(),
                    'registered_date' => optional($album->registered_date)->format('Y-m-d'),
                ];
            }),

            'payoutRequests' => $user->payoutRequests->sortByDesc('created_at')->values()->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => number_format((float) $payout->amount, 2),
                    'currency' => $payout->currency,
                    'status' => $payout->status,
                    'created_at' => optional($payout->created_at)->format('Y-m-d H:i'),
                    'payment_method' => $payout->paymentMethod ? $this->paymentTypeLabel($payout->paymentMethod->type) : null,
                ];
            }),

            'takedownRequests' => $user->takedownRequests->sortByDesc('created_at')->values()->map(function ($request) {
                return [
                    'id' => $request->id,
                    'song_name' => $request->song_name,
                    'album_name' => $request->album_name,
                    'status' => $request->status,
                    'created_at' => optional($request->created_at)->format('Y-m-d H:i'),
                ];
            }),

            'status' => session('status'),
        ]);
    }

    public function updateWallet(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'user', 404);

        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'action' => ['required', 'in:add,subtract,set'],
        ]);

        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'currency' => 'MMK',
            ]
        );

        $amount = (float) $validated['amount'];

        if ($validated['action'] === 'add') {
            $wallet->balance += $amount;
            $wallet->total_earned += max($amount, 0);
        }

        if ($validated['action'] === 'subtract') {
            $wallet->balance = max(0, $wallet->balance - abs($amount));
        }

        if ($validated['action'] === 'set') {
            $wallet->balance = max(0, $amount);
        }

        $wallet->save();

        return back()->with('status', 'Wallet updated successfully.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'user', 404);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update([
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('status', 'User status updated successfully.');
    }

    private function paymentTypeLabel(?string $type): string
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