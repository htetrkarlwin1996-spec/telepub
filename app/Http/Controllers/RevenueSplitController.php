<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\RevenueSplitChangeRequest;
use App\Services\AdminNotifier;
use App\Services\RevenueSplitService;
use App\Services\UserNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RevenueSplitController extends Controller
{
    public function adminIndex()
    {
        $releases = Album::with(['artist.masterAccounts', 'collaboratingArtists', 'splitVersions'])
            ->whereHas('collaboratingArtists')->latest()->paginate(25);
        $requests = RevenueSplitChangeRequest::with(['album.artist'])->where('status', 'pending')->latest()->get();

        return view('admin.revenue-splits.index', compact('releases', 'requests'));
    }

    public function requestChange(Request $request, Album $album, RevenueSplitService $splits, AdminNotifier $notifier, UserNotifier $userNotifier)
    {
        abort_unless($album->artist_id === current_artist()?->id, 403);
        abort_unless($album->splitsAreLocked(), 422, 'Revenue shares are not locked yet and may be edited on the release.');
        $validated = $this->validateProposed($request, $album);

        RevenueSplitChangeRequest::create([
            'album_id' => $album->id,
            'requested_by' => $request->user()->id,
            'current_splits' => $splits->snapshot($album),
            'proposed_splits' => $this->proposedSnapshot($album, $validated, $splits),
            'reason' => $validated['reason'],
        ]);
        $notifier->activity('revenue_split_change_requested', 'Revenue split change requested', $album->artist->artist_name.' requested a collaborator share change for “'.$album->title.'”.', route('admin.revenue-splits.index'), [
            'Artist' => $album->artist->artist_name,
            'Release' => $album->title,
            'Reason' => $validated['reason'],
        ]);
        $userNotifier->activity($request->user(), 'Collaborator share change requested', 'Your collaborator share change request for “'.$album->title.'” was sent to Admin for review.', route('artist.catalog.show', $album), 'View Release');

        return back()->with('success', 'Revenue split change request sent to Admin.');
    }

    public function approve(Request $request, RevenueSplitChangeRequest $changeRequest, RevenueSplitService $splits, UserNotifier $notifier)
    {
        abort_unless($changeRequest->status === 'pending', 422);
        $validated = $request->validate(['admin_note' => ['nullable', 'string', 'max:2000']]);

        DB::transaction(function () use ($changeRequest, $splits, $request, $validated) {
            $proposed = $changeRequest->proposed_splits;
            $pivot = collect($proposed['collaborators'] ?? [])->mapWithKeys(fn ($split) => [
                $split['artist_id'] => ['role' => 'collaborator', 'share_percentage' => $split['percentage']],
            ])->all();
            $changeRequest->album->collaboratingArtists()->sync($pivot);
            $splits->lock($changeRequest->album->fresh(), $request->user());
            $changeRequest->update([
                'status' => 'approved', 'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(), 'admin_note' => $validated['admin_note'] ?? null,
            ]);
        });
        $notifier->splitChanged($changeRequest->album->fresh(), 'approved', $validated['admin_note'] ?? null);

        return back()->with('success', 'Revenue split change approved for future royalties.');
    }

    public function reject(Request $request, RevenueSplitChangeRequest $changeRequest, UserNotifier $notifier)
    {
        abort_unless($changeRequest->status === 'pending', 422);
        $validated = $request->validate(['admin_note' => ['required', 'string', 'max:2000']]);
        $changeRequest->update([
            'status' => 'rejected', 'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(), 'admin_note' => $validated['admin_note'],
        ]);
        $notifier->splitChanged($changeRequest->album, 'rejected', $validated['admin_note']);

        return back()->with('success', 'Revenue split change rejected.');
    }

    private function validateProposed(Request $request, Album $album): array
    {
        $validated = $request->validate([
            'collaborating_artists' => ['nullable', 'array'],
            'collaborating_artists.*' => ['required', 'distinct', 'exists:artists,id', Rule::notIn([$album->artist_id])],
            'collaborating_shares' => ['nullable', 'array'],
            'collaborating_shares.*' => ['required', 'numeric', 'gt:0', 'max:100'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        if (count($validated['collaborating_artists'] ?? []) !== count($validated['collaborating_shares'] ?? [])) {
            throw ValidationException::withMessages(['collaborating_shares' => 'Every collaborator requires a share.']);
        }
        if (array_sum($validated['collaborating_shares'] ?? []) > 100) {
            throw ValidationException::withMessages(['collaborating_shares' => 'Collaborator shares may not total more than 100%.']);
        }

        return $validated;
    }

    private function proposedSnapshot(Album $album, array $validated, RevenueSplitService $splits): array
    {
        $snapshot = $splits->snapshot($album);
        $artists = Artist::whereIn('id', $validated['collaborating_artists'] ?? [])->pluck('artist_name', 'id');
        $snapshot['collaborators'] = collect($validated['collaborating_artists'] ?? [])->map(fn ($artistId, $index) => [
            'artist_id' => (int) $artistId,
            'artist_name' => $artists[$artistId],
            'percentage' => (float) $validated['collaborating_shares'][$index],
        ])->all();
        $snapshot['primary_artist_percentage'] = 100 - collect($snapshot['collaborators'])->sum('percentage');

        return $snapshot;
    }
}
