<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id', 'song_id', 'album_id', 'store_id', 'month', 'year',
        'streams', 'downloads', 'likes', 'playlist_adds', 'revenue'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

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
}
