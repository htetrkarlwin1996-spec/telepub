<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id', 'withdrawal_id', 'invoice_number', 'amount', 'fee', 'total',
        'currency', 'status', 'period_start', 'period_end', 'paid_at',
        'payment_method', 'payment_reference', 'notes', 'processed_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function setCurrencyAttribute(mixed $value): void
    {
        $this->attributes['currency'] = 'USD';
    }
}
