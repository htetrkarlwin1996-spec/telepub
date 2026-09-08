<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakedownRequest extends Model
{
    protected $fillable = [
        'user_id',
        'song_name',
        'album_name',
        'links',
        'reason',
        'status',
        'admin_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}