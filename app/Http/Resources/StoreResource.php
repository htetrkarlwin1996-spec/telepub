<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'logo'        => $this->logo,
            'description' => $this->description,
            'url'         => $this->url,
            'is_active'   => (bool) $this->is_active,
        ];
    }
}
