<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaboratingArtistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'artist_name' => $this->artist_name,
            'pivot'       => [
                'share_percentage' => (float) $this->pivot->share_percentage,
                'role'             => $this->pivot->role,
            ],
        ];
    }
}
