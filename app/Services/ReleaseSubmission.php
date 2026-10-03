<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Distribution;

class ReleaseSubmission
{
    public function __construct(
        private readonly RevenueSplitService $splits,
        private readonly AdminNotifier $notifier,
        private readonly UserNotifier $userNotifier,
    ) {}

    public function finalize(Album $album): void
    {
        foreach ($album->selected_store_ids ?? [] as $storeId) {
            foreach ($album->songs as $song) {
                Distribution::firstOrCreate(
                    ['song_id' => $song->id, 'album_id' => $album->id, 'store_id' => $storeId, 'artist_id' => $album->artist_id],
                    ['status' => 'submitted', 'submitted_at' => now(), 'distribution_fee' => 0]
                );
            }
        }
        if (! $album->splitsAreLocked()) {
            $this->splits->lock($album, auth()->user());
        }
        $album->update(['payment_status' => 'paid', 'status' => 'submitted']);
        $album->songs()->update(['status' => 'submitted']);
        $this->notifier->releaseSubmitted($album->fresh());
        $this->userNotifier->releaseSubmitted($album->fresh());
        if ($album->collaboratingArtists()->exists()) {
            $this->userNotifier->splitChanged($album->fresh(), 'confirmed');
        }
    }
}
