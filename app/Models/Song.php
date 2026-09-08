<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id', 'artist_id', 'title', 'version', 'track_number', 'duration',
        'file_path', 'audio_file', 'isrc_code', 'request_new_isrc', 'explicit', 'genre',
        'language', 'primary_artists', 'composers', 'lyricist', 'producers', 'vocals',
        'featuring', 'lyrics', 'status'
    ];

    protected $casts = [
        'explicit' => 'boolean',
        'request_new_isrc' => 'boolean',
        'primary_artists' => 'array',
        'composers' => 'array',
        'lyricist' => 'array',
        'producers' => 'array',
        'vocals' => 'array',
        'featuring' => 'array',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    public function royalties()
    {
        return $this->hasMany(Royalty::class);
    }
}
