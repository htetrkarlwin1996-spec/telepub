<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'artist_id', 'amount', 'currency', 'type',
        'status', 'issue_date', 'due_date', 'paid_date', 'description',
        'notes', 'created_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function setCurrencyAttribute(mixed $value): void
    {
        $this->attributes['currency'] = 'USD';
    }

}
