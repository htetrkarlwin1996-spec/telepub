<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ReleaseTakedownRequest;
use App\Services\AdminNotifier;
use App\Services\UserNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReleaseTakedownController extends Controller
{
    public function store(Request $request, Album $album, AdminNotifier $notifier)
    {
        abort_unless($album->artist_id === current_artist()?->id, 403);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        if ($album->status !== 'approved' || $album->taken_down_at) {
            throw ValidationException::withMessages(['reason' => 'Only an active approved release can be taken down.']);
        }

        $takedown = DB::transaction(function () use ($album, $request, $validated) {
            $lockedAlbum = Album::query()->lockForUpdate()->findOrFail($album->id);
            if ($lockedAlbum->takedownRequests()->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['reason' => 'A take down request is already pending for this release.']);
            }

            return $lockedAlbum->takedownRequests()->create([
                'artist_id' => $lockedAlbum->artist_id,
                'requested_by_user_id' => $request->user()->id,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);
        });

        $notifier->activity('release_takedown_requested', 'Release take down requested',
            ($album->artist?->artist_name ?? 'Artist').' requested take down for “'.$album->title.'”.',
            route('admin.releases.show', $album), ['Reason' => $takedown->reason]);

        return back()->with('success', 'Take down request sent to Admin for review.');
    }

    public function approve(Request $request, ReleaseTakedownRequest $takedownRequest, UserNotifier $notifier)
    {
        $validated = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $takedownRequest, $validated) {
            $item = ReleaseTakedownRequest::query()->lockForUpdate()->findOrFail($takedownRequest->id);
            abort_unless($item->status === 'pending', 409, 'This request was already reviewed.');
            $now = now();
            $item->update(['status' => 'approved', 'admin_notes' => $validated['admin_notes'] ?? null,
                'reviewed_by_user_id' => $request->user()->id, 'reviewed_at' => $now]);
            $item->album()->update(['taken_down_at' => $now]);
            $item->album->distributions()->update(['taken_down_at' => $now]);
        });
        $notifier->takedownStatus($takedownRequest->fresh('album.artist.user'));

        return back()->with('success', 'Take down request approved.');
    }

    public function reject(Request $request, ReleaseTakedownRequest $takedownRequest, UserNotifier $notifier)
    {
        $validated = $request->validate(['admin_notes' => ['required', 'string', 'max:2000']]);
        abort_unless($takedownRequest->status === 'pending', 409, 'This request was already reviewed.');
        $takedownRequest->update(['status' => 'rejected', 'admin_notes' => $validated['admin_notes'],
            'reviewed_by_user_id' => $request->user()->id, 'reviewed_at' => now()]);
        $notifier->takedownStatus($takedownRequest->fresh('album.artist.user'));

        return back()->with('success', 'Take down request rejected.');
    }
}
