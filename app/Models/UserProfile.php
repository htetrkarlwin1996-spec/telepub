<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'artist_name',
        'publisher_name',
        'ipi_name',
        'ipi_number',
        'country',
        'city',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}