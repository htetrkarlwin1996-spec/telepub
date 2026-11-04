<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseTakedownRequest extends Model
{
    protected $fillable = [
        'album_id', 'artist_id', 'requested_by_user_id', 'reason', 'status',
        'admin_notes', 'reviewed_by_user_id', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
