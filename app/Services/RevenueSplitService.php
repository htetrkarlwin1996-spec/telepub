<?php

namespace App\Services;

use App\Models\Album;
use App\Models\RevenueSplitVersion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RevenueSplitService
{
    public function snapshot(Album $album): array
    {
        $album->loadMissing('artist.masterAccounts', 'collaboratingArtists');
        $master = $album->artist->activeMasterAccount();
        $platformFee = $master
            ? (float) $master->platform_fee_percentage
            : 100 - (float) $album->artist->revenue_share_percentage;
        $managementFee = $master ? $master->managementFeeFor($album->artist) : 0;
        $collaborators = $album->collaboratingArtists->map(fn ($artist) => [
            'artist_id' => $artist->id,
            'artist_name' => $artist->artist_name,
            'percentage' => (float) $artist->pivot->share_percentage,
        ])->values()->all();
        $collaboratorTotal = collect($collaborators)->sum('percentage');

        if ($collaboratorTotal > 100.0001) {
            throw ValidationException::withMessages([
                'collaborating_shares' => 'Collaborator shares may not total more than 100%.',
            ]);
        }

        return [
            'platform_fee_percentage' => $platformFee,
            'master_account_id' => $master?->id,
            'master_account_name' => $master?->name,
            'management_fee_percentage' => $managementFee,
            'primary_artist_id' => $album->artist_id,
            'primary_artist_name' => $album->artist->artist_name,
            'primary_artist_percentage' => max(0, 100 - $collaboratorTotal),
            'collaborators' => $collaborators,
        ];
    }

    public function lock(Album $album, ?User $approvedBy = null): RevenueSplitVersion
    {
        $snapshot = $this->snapshot($album);
        $latest = $album->splitVersions()->max('version') ?? 0;
        $version = RevenueSplitVersion::create([
            'album_id' => $album->id,
            'version' => $latest + 1,
            'splits' => $snapshot,
            'effective_from' => now(),
            'locked_at' => now(),
            'approved_by' => $approvedBy?->id,
        ]);
        $album->update(['split_locked_at' => now(), 'split_locked_by' => $approvedBy?->id]);

        return $version;
    }

    public function versionFor(Album $album, $at = null): ?RevenueSplitVersion
    {
        return $album->splitVersions()
            ->where('effective_from', '<=', $at ?? now())
            ->latest('effective_from')
            ->first();
    }
}
