<?php

namespace App\Http\Controllers;

use App\Models\MasterAccount;
use App\Models\User;
use App\Services\UserNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminMasterAccountController extends Controller
{
    public function index()
    {
        $accounts = MasterAccount::with('owner')->withCount('artists')->latest()->paginate(20);

        return view('admin.master-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.master-accounts.create');
    }

    public function store(Request $request, UserNotifier $notifier)
    {
        $validated = $this->validated($request, true);
        $owner = DB::transaction(function () use ($validated) {
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['password']),
            ]);
            $owner->forceFill(['role' => 'manager', 'is_active' => true, 'email_verified_at' => now()])->save();
            MasterAccount::create([
                'owner_user_id' => $owner->id,
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $validated['owner_email'],
                'country' => $validated['country'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'platform_fee_percentage' => $validated['platform_fee_percentage'],
                'default_management_fee_percentage' => $validated['default_management_fee_percentage'],
                'maximum_management_fee_percentage' => $validated['maximum_management_fee_percentage'],
            ]);

            return $owner;
        });
        $notifier->accountCreated($owner, 'Master Account');

        return redirect()->route('admin.master-accounts.index')->with('success', 'Master account created.');
    }

    public function edit(MasterAccount $masterAccount)
    {
        $masterAccount->load('owner', 'artists.user');

        return view('admin.master-accounts.edit', compact('masterAccount'));
    }

    public function update(Request $request, MasterAccount $masterAccount, UserNotifier $notifier)
    {
        $validated = $this->validated($request, false, $masterAccount);
        $masterAccount->update(collect($validated)->except(['owner_name', 'owner_email', 'password'])->all());
        $ownerData = ['name' => $validated['owner_name'], 'email' => $validated['owner_email']];
        if (filled($validated['password'] ?? null)) {
            $ownerData['password'] = Hash::make($validated['password']);
        }
        $masterAccount->owner->update($ownerData);
        $notifier->activity($masterAccount->owner, 'Master Account updated', 'Admin updated your TeleMusic Master Account settings.', route('dashboard'), 'Review Account');
        if (filled($validated['password'] ?? null)) {
            $notifier->activity($masterAccount->owner, 'Password changed', 'Admin changed your TeleMusic Master Account password.', route('login'), 'Sign In');
        }

        return back()->with('success', 'Master account fees updated.');
    }

    private function validated(Request $request, bool $creating, ?MasterAccount $masterAccount = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'unique:users,email,'.$masterAccount?->owner_user_id],
            'password' => [$creating ? 'required' : 'nullable', 'min:8'],
            'email' => ['nullable', 'email'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'platform_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_management_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'maximum_management_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:default_management_fee_percentage'],
        ]);
    }
}
