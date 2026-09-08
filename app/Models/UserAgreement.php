<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAgreement extends Model
{
    protected $fillable = [
        'user_id',
        'agreement_name',
        'signed_name',
        'signed_date',
        'pdf_path',
    ];

    protected $casts = [
        'signed_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}