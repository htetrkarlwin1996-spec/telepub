<?php

namespace App\Models;

use App\Services\RoyaltyAllocationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Royalty extends Model
{
    use HasFactory;

    public const TYPES = [
        'royalties' => 'Royalties',
        'publishing_rights' => 'Global Performance Royalties',
        'composer_rights' => 'Composer / Songwriter Royalties',
        'mechanical_royalties' => 'Mechanical Royalties',
    ];

    protected $fillable = [
        'artist_id', 'song_id', 'album_id', 'store_id', 'royalty_type', 'month', 'year',
        'amount', 'currency', 'exchange_rate', 'streams', 'notes', 'entered_by', 'royalty_import_id', 'import_row',
    ];

    protected $appends = ['royalty_type_label'];

    protected static function booted(): void
    {
        static::created(function (Royalty $royalty) {
            if (Schema::hasTable('royalty_allocations')) {
                app(RoyaltyAllocationService::class)->allocate($royalty);
            }
        });
    }

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

    public function allocations()
    {
        return $this->hasMany(RoyaltyAllocation::class);
    }

    public function amountForArtist(Artist $artist): float
    {
        if (array_key_exists('artist_amount', $this->attributes)) {
            return (float) $this->attributes['artist_amount'];
        }

        $allocation = $this->relationLoaded('allocations')
            ? $this->allocations->first(fn ($item) => $item->beneficiary_type === 'artist' && (int) $item->beneficiary_id === $artist->id)
            : $this->allocations()->where('beneficiary_type', 'artist')->where('beneficiary_id', $artist->id)->first();

        return (float) ($allocation?->allocated_amount ?? 0);
    }

    public function setCurrencyAttribute(mixed $value): void
    {
        $this->attributes['currency'] = 'USD';
    }
}
