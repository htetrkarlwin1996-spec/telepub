<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'song_id'          => $this->song_id,
            'album_id'         => $this->album_id,
            'store_id'         => $this->store_id,
            'artist_id'        => $this->artist_id,
            'status'           => $this->status,
            'store_url'        => $this->store_url,
            'submitted_at'     => $this->submitted_at,
            'approved_at'      => $this->approved_at,
            'live_at'          => $this->live_at,
            'store'            => new StoreResource($this->whenLoaded('store')),
        ];
    }
}
