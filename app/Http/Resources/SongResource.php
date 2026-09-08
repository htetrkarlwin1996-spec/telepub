<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'album_id'         => $this->album_id,
            'artist_id'        => $this->artist_id,
            'title'            => $this->title,
            'version'          => $this->version,
            'track_number'     => $this->track_number,
            'duration'         => $this->duration,
            'audio_file'       => $this->audio_file,
            'isrc_code'        => $this->isrc_code,
            'request_new_isrc' => (bool) $this->request_new_isrc,
            'explicit'         => (bool) $this->explicit,
            'genre'            => $this->genre,
            'language'         => $this->language,
            'primary_artists'  => $this->primary_artists,
            'composers'        => $this->composers,
            'producers'        => $this->producers,
            'vocals'           => $this->vocals,
            'featuring'        => $this->featuring,
            'lyrics'           => $this->lyrics,
            'status'           => $this->status,
        ];
    }
}
