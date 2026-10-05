<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\RoyaltyAllocation;
use App\Models\User;
use App\Services\UserNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterAccountController extends Controller
{
    public function dashboard(Request $request)
    {
        $account = $this->account($request);
        $artists = $account->artists()->withCount(['albums', 'songs'])->get();
        $artistIds = $artists->pluck('id');
        $artistEarnings = RoyaltyAllocation::where('beneficiary_type', 'artist')
            ->whereIn('beneficiary_id', $artistIds)->sum('allocated_amount');
        $earningsByArtist = RoyaltyAllocation::where('beneficiary_type', 'artist')
            ->whereIn('beneficiary_id', $artistIds)
            ->selectRaw('beneficiary_id, SUM(allocated_amount) as total')
            ->groupBy('beneficiary_id')->pluck('total', 'beneficiary_id');

        return view('manager.dashboard', compact('account', 'artists', 'artistEarnings', 'earningsByArtist'));
    }

    public function createArtist(Request $request)
    {
        $account = $this->account($request);

        return view('manager.artists.create', compact('account'));
    }

    public function storeArtist(Request $request, UserNotifier $notifier)
    {
        $account = $this->account($request);
        $validated = $request->validate([
            'artist_name' => ['required', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'required_with:email', 'min:8'],
            'management_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:'.$account->maximum_management_fee_percentage],
            'access_level' => ['required', Rule::in(['report_only', 'full_access'])],
        ]);

        $user = null;
        if (filled($validated['email'] ?? null)) {
            $user = User::create([
                'name' => $validated['artist_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'artist',
            ]);
            $user->forceFill([
                'role' => 'artist',
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }
        $artist = Artist::create([
            'user_id' => $user?->id,
            'created_by_user_id' => $request->user()->id,
            'artist_name' => $validated['artist_name'],
            'genre' => $validated['genre'] ?? null,
            'country' => $validated['country'] ?? null,
            'revenue_share_percentage' => 85,
        ]);
        $account->artists()->attach($artist->id, [
            'management_fee_percentage' => $validated['management_fee_percentage'] ?? null,
            'access_level' => $validated['access_level'],
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);
        if ($user) {
            $notifier->accountCreated($user, 'Artist');
        }

        return redirect()->route('manager.dashboard')->with('success', 'Managed artist created.');
    }

    public function updateArtist(Request $request, Artist $artist, UserNotifier $notifier)
    {
        $account = $this->account($request);
        abort_unless($account->artists()->whereKey($artist->id)->exists(), 403);
        $validated = $request->validate([
            'management_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:'.$account->maximum_management_fee_percentage],
            'access_level' => ['required', Rule::in(['report_only', 'full_access'])],
        ]);
        $account->artists()->updateExistingPivot($artist->id, $validated);
        $notifier->activity($artist->user, 'Managed artist access updated', 'Your label/master account updated your TeleMusic access level or management fee.', route('dashboard'), 'Review Account', [
            'Access level' => str_replace('_', ' ', $validated['access_level']),
            'Management fee' => isset($validated['management_fee_percentage']) ? $validated['management_fee_percentage'].'%' : 'Account default',
        ]);

        return back()->with('success', 'Artist access and fee updated.');
    }

    public function selectArtist(Request $request, Artist $artist)
    {
        $account = $this->account($request);
        abort_unless($account->artists()->wherePivot('status', 'active')->whereKey($artist->id)->exists(), 403);
        $request->session()->put('managed_artist_id', $artist->id);

        return redirect()->route('artist.catalog.index')->with('success', 'Now managing '.$artist->artist_name.'.');
    }

    private function account(Request $request)
    {
        abort_unless($request->user()->isManager(), 403);

        return $request->user()->masterAccount()->where('is_active', true)->firstOrFail();
    }
}
