<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_id', 'album_id', 'store_id', 'artist_id', 'status',
        'distribution_fee', 'store_url', 'submitted_at', 'approved_at',
        'live_at', 'notes'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'live_at' => 'datetime',
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function store()
    {
        return $this->belongsTo(MusicStore::class, 'store_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
