<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id', 'title', 'slug', 'release_type', 'cover_art', 'genre', 'label',
        'release_date', 'physical_release_date', 'price', 'upc_code', 'status',
        'copyright_holder', 'phonogram_right_holder', 'request_new_isrc',
        'approved_at', 'rejected_at', 'rejection_reason', 'notes'
    ];

    protected $casts = [
        'release_date' => 'date',
        'physical_release_date' => 'date',
        'price' => 'decimal:2',
        'request_new_isrc' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * Collaborating artists on this album (via pivot table).
     * The primary artist (albums.artist_id) is NOT in this pivot — only collaborators.
     */
    public function collaboratingArtists()
    {
        return $this->belongsToMany(Artist::class, 'album_artist')
            ->withPivot('share_percentage', 'role')
            ->withTimestamps()
            ->wherePivot('role', 'collaborator');
    }

    /**
     * All artists associated with this album, including the primary artist.
     * Returns a collection with 'artist', 'share_percentage', and 'role' for each.
     */
    public function getAllArtistsAttribute()
    {
        $primary = $this->relationLoaded('artist') ? $this->artist : $this->artist()->first();
        $collabs = $this->relationLoaded('collaboratingArtists') ? $this->collaboratingArtists : $this->collaboratingArtists()->get();

        $artists = collect();

        if ($primary) {
            $artists->push((object) [
                'artist' => $primary,
                'role' => 'primary',
                'share_percentage' => null, // primary gets remainder
            ]);
        }

        foreach ($collabs as $collab) {
            $artists->push((object) [
                'artist' => $collab,
                'role' => $collab->pivot->role,
                'share_percentage' => $collab->pivot->share_percentage,
            ]);
        }

        return $artists;
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    public function royalties()
    {
        return $this->hasMany(Royalty::class);
    }

    public function stores()
    {
        return $this->belongsToMany(MusicStore::class, 'distributions', 'album_id', 'store_id')
            ->withPivot('status', 'submitted_at', 'approved_at', 'live_at')
            ->withTimestamps();
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isRejected(): bool
    {
        return !is_null($this->rejected_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'submitted' && !$this->isApproved() && !$this->isRejected();
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'approved');
    }
}
