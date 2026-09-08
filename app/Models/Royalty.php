<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Royalty extends Model
{
    use HasFactory;

    public const TYPES = [
        'royalties' => 'Royalties',
        'publishing_rights' => 'Publishing Rights',
        'composer_rights' => 'Composer Rights',
        'mechanical_royalties' => 'Mechanical Royalties',
    ];

    protected $fillable = [
        'artist_id', 'song_id', 'album_id', 'store_id', 'royalty_type', 'month', 'year',
        'amount', 'currency', 'exchange_rate', 'streams', 'notes', 'entered_by',
    ];

    protected $appends = ['royalty_type_label'];

    public function getRoyaltyTypeLabelAttribute(): string
    {
        return self::TYPES[$this->royalty_type ?? 'royalties'] ?? self::TYPES['royalties'];
    }

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

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
