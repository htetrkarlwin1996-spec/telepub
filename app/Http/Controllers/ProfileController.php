<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request, UserSetupService $userSetupService): Response
    {
        $currentUser = $request->user();

        $userSetupService->createDefaults($currentUser);

        $currentUser->load(['profile', 'agreement', 'wallet']);

        return Inertia::render('Profile/Edit', [
            'status' => session('status'),

            'user' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
            ],

            'profile' => $currentUser->profile ? [
                'display_name' => $currentUser->profile->display_name,
                'artist_name' => $currentUser->profile->artist_name,
                'publisher_name' => $currentUser->profile->publisher_name,
                'ipi_name' => $currentUser->profile->ipi_name,
                'ipi_number' => $currentUser->profile->ipi_number,
                'country' => $currentUser->profile->country,
                'city' => $currentUser->profile->city,
                'address' => $currentUser->profile->address,
            ] : null,

            'agreement' => $currentUser->agreement ? [
                'agreement_name' => $currentUser->agreement->agreement_name,
                'signed_name' => $currentUser->agreement->signed_name,
                'signed_date' => optional($currentUser->agreement->signed_date)->format('Y-m-d'),
                'url' => asset('storage/' . $currentUser->agreement->pdf_path),
            ] : null,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $currentUser = $request->user();

        $validated = $request->validated();

        $currentUser->profile()->updateOrCreate(
            ['user_id' => $currentUser->id],
            [
                'display_name' => $validated['display_name'] ?? $currentUser->name,
                'artist_name' => $validated['artist_name'] ?? null,
                'publisher_name' => $validated['publisher_name'] ?? null,
                'ipi_name' => $validated['ipi_name'] ?? null,
                'ipi_number' => $validated['ipi_number'] ?? null,
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
            ]
        );

        return Redirect::route('profile.edit')
            ->with('status', 'Publishing profile updated successfully.');
    }

    public function regenerateAgreement(Request $request, UserSetupService $userSetupService): RedirectResponse
    {
        $userSetupService->regenerateAgreement($request->user());

        return Redirect::route('profile.edit')
            ->with('status', 'Signed agreement PDF regenerated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $currentUser = $request->user();

        auth()->logout();

        $currentUser->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}