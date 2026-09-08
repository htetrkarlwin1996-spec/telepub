<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = [
        'user_id',
        'album_name',
        'artist_name',
        'cover_image',
        'status',
        'registration_type',
        'registration_fee',
        'registered_date',
        'admin_note',
    ];

    protected $casts = [
        'registered_date' => 'date',
        'registration_fee' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}