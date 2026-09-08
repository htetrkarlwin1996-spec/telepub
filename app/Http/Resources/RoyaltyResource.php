<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoyaltyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'artist_id' => $this->artist_id,
            'song_id' => $this->song_id,
            'album_id' => $this->album_id,
            'store_id' => $this->store_id,
            'royalty_type' => $this->royalty_type,
            'royalty_type_label' => $this->royalty_type_label,
            'month' => $this->month,
            'year' => $this->year,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'streams' => $this->streams,
            'notes' => $this->notes,
            'store' => new StoreResource($this->whenLoaded('store')),
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'album' => new AlbumResource($this->whenLoaded('album')),
            'song' => new SongResource($this->whenLoaded('song')),
        ];
    }
}
