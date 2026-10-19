<?php

namespace App\Services;

use App\Jobs\DeliverReleaseToTeleMusicApp;
use App\Models\Album;

class TeleMusicAppDelivery
{
    public function dispatchIfSelected(Album $album): bool
    {
        $selected = $album->distributions()
            ->whereHas('store', fn ($query) => $query->where('delivery_driver', 'telemusic_api'))
            ->exists();

        if (! $selected || $album->status !== 'approved') {
            return false;
        }

        DeliverReleaseToTeleMusicApp::dispatch($album->id)->afterCommit();

        return true;
    }
}
