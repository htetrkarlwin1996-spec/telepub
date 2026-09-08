<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodController extends Controller
{
    public function index(Request $request): Response
    {
        $methods = $request->user()
            ->paymentMethods()
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
                    'bank_branch' => $method->bank_branch,
                    'is_default' => (bool) $method->is_default,
                    'created_at' => optional($method->created_at)->format('Y-m-d'),
                ];
            });

        return Inertia::render('User/PaymentMethods/Index', [
            'methods' => $methods,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $validated = $this->normalizeByType($validated);

        $user = $request->user();

        if (($validated['is_default'] ?? false) || $user->paymentMethods()->count() === 0) {
            $user->paymentMethods()->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $user->paymentMethods()->create($validated);

        return back()->with('status', 'Payment method added successfully.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        abort_unless($paymentMethod->user_id === $request->user()->id, 403);

        $validated = $this->validateData($request);
        $validated = $this->normalizeByType($validated);

        if ($validated['is_default'] ?? false) {
            $request->user()->paymentMethods()->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = $paymentMethod->is_default;
        }

        $paymentMethod->update($validated);

        return back()->with('status', 'Payment method updated successfully.');
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        abort_unless($paymentMethod->user_id === $request->user()->id, 403);

        $wasDefault = $paymentMethod->is_default;

        $paymentMethod->delete();

        if ($wasDefault) {
            $nextMethod = $request->user()->paymentMethods()->latest()->first();

            if ($nextMethod) {
                $nextMethod->update(['is_default' => true]);
            }
        }

        return back()->with('status', 'Payment method deleted successfully.');
    }

    public function setDefault(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        abort_unless($paymentMethod->user_id === $request->user()->id, 403);

        $request->user()->paymentMethods()->update(['is_default' => false]);

        $paymentMethod->update(['is_default' => true]);

        return back()->with('status', 'Default payment method updated.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['kbz_bank', 'thai_bank', 'kbz_pay', 'wave_pay'])],

            'bank_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('type') === 'thai_bank'),
            ],

            'account_name' => ['required', 'string', 'max:255'],

            'account_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => in_array($request->input('type'), ['kbz_bank', 'thai_bank'], true)),
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => in_array($request->input('type'), ['kbz_pay', 'wave_pay'], true)),
            ],

            'bank_branch' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function normalizeByType(array $data): array
    {
        if (in_array($data['type'], ['kbz_bank', 'thai_bank'], true)) {
            $data['phone_number'] = null;
        }

        if (in_array($data['type'], ['kbz_pay', 'wave_pay'], true)) {
            $data['bank_name'] = null;
            $data['account_number'] = null;
            $data['bank_branch'] = null;
        }

        if ($data['type'] === 'kbz_bank') {
            $data['bank_name'] = 'KBZ Bank';
        }

        return $data;
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