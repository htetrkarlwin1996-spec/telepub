<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'album_id',
        'user_id',
        'song_name',
        'artist_name',
        'registered_date',
        'bmi_work_id',
        'bmi_ipi_name',
    ];

    protected $casts = [
        'registered_date' => 'date',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}