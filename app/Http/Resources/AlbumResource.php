<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'artist_id'              => $this->artist_id,
            'title'                  => $this->title,
            'slug'                   => $this->slug,
            'release_type'           => $this->release_type,
            'cover_art'              => $this->cover_art,
            'genre'                  => $this->genre,
            'label'                  => $this->label,
            'release_date'           => $this->release_date,
            'physical_release_date'  => $this->physical_release_date,
            'price'                  => (float) $this->price,
            'upc_code'               => $this->upc_code,
            'status'                 => $this->status,
            'copyright_holder'       => $this->copyright_holder,
            'phonogram_right_holder' => $this->phonogram_right_holder,
            'request_new_isrc'       => (bool) $this->request_new_isrc,
            'approved_at'            => $this->approved_at,
            'rejected_at'            => $this->rejected_at,
            'rejection_reason'       => $this->rejection_reason,
            'notes'                  => $this->notes,
            'created_at'             => $this->created_at,
            'artist'                 => new ArtistResource($this->whenLoaded('artist')),
            'songs'                  => SongResource::collection($this->whenLoaded('songs')),
            'collaborating_artists'  => CollaboratingArtistResource::collection($this->whenLoaded('collaboratingArtists')),
            'stores'                 => StoreResource::collection($this->whenLoaded('stores')),
            'distributions'          => DistributionResource::collection($this->whenLoaded('distributions')),
        ];
    }
}
