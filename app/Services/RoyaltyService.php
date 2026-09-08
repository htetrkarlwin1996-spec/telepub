<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Royalty;
use Illuminate\Support\Facades\DB;

class RoyaltyService
{
    public function create(array $attributes): Royalty
    {
        return DB::transaction(function () use ($attributes) {
            $royalty = Royalty::create($attributes);
            $artist = Artist::findOrFail($royalty->artist_id);
            $share = $artist->getArtistShareAttribute($royalty->amount);

            $artist->increment('total_earnings', $share);
            $artist->increment('available_balance', $share);

            return $royalty;
        });
    }

    public function update(Royalty $royalty, array $attributes): Royalty
    {
        return DB::transaction(function () use ($royalty, $attributes) {
            $oldArtist = Artist::findOrFail($royalty->artist_id);
            $oldShare = $oldArtist->getArtistShareAttribute($royalty->amount);

            $royalty->update($attributes);
            $newArtist = Artist::findOrFail($royalty->artist_id);
            $newShare = $newArtist->getArtistShareAttribute($royalty->amount);

            if ($oldArtist->is($newArtist)) {
                $difference = $newShare - $oldShare;
                $newArtist->increment('total_earnings', $difference);
                $newArtist->increment('available_balance', $difference);
            } else {
                $oldArtist->decrement('total_earnings', $oldShare);
                $oldArtist->decrement('available_balance', $oldShare);
                $newArtist->increment('total_earnings', $newShare);
                $newArtist->increment('available_balance', $newShare);
            }

            return $royalty->fresh();
        });
    }
}
