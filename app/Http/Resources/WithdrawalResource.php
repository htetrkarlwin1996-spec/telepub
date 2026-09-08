<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'artist_id'       => $this->artist_id,
            'amount'          => (float) $this->amount,
            'fee'             => (float) $this->fee,
            'total'           => (float) $this->total,
            'currency'        => $this->currency,
            'status'          => $this->status,
            'payment_method'  => $this->payment_method,
            'payment_details' => $this->payment_details,
            'notes'           => $this->notes,
            'admin_notes'     => $this->admin_notes,
            'requested_at'    => $this->created_at,
            'processed_at'    => $this->processed_at,
            'artist'          => new ArtistResource($this->whenLoaded('artist')),
        ];
    }
}
