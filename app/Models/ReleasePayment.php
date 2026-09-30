<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleasePayment extends Model
{
    protected $fillable = ['album_id', 'user_id', 'provider', 'status', 'amount', 'currency', 'reference', 'checkout_url', 'qr_data', 'gateway_response', 'paid_at'];

    protected $casts = ['amount' => 'decimal:2', 'gateway_response' => 'array', 'paid_at' => 'datetime'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
