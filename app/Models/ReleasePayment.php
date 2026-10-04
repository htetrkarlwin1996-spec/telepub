<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleasePayment extends Model
{
    protected $fillable = ['album_id', 'user_id', 'provider', 'status', 'amount', 'currency', 'reference', 'addon_services', 'checkout_url', 'qr_data', 'expires_at', 'gateway_response', 'paid_at'];

    protected $casts = ['amount' => 'decimal:2', 'addon_services' => 'array', 'gateway_response' => 'array', 'expires_at' => 'datetime', 'paid_at' => 'datetime'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
