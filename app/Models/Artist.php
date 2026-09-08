<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'artist_name', 'genre', 'bio', 'country',
        'avatar', 'banner', 'payment_email', 'paypal_email',
        'bank_account_info',
        // Bank Transfer
        'bank_account_name', 'bank_name', 'bank_country', 'bank_account_no',
        // KBZ Pay
        'kbz_pay_name', 'kbz_pay_phone',
        // Wave Pay
        'wave_pay_name', 'wave_pay_phone',
        // Profile Links
        'spotify_profile_url', 'apple_music_profile_url',
        'youtube_profile_url', 'tidal_profile_url',
        'total_earnings', 'available_balance', 'pending_balance',
        // Revenue Share
        'revenue_share_percentage',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    /**
     * Albums where this artist is a collaborator (via pivot table).
     */
    public function collaboratedAlbums()
    {
        return $this->belongsToMany(Album::class, 'album_artist')
            ->withPivot('share_percentage', 'role')
            ->withTimestamps();
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

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytics::class);
    }

    // ===== REVENUE SHARE HELPERS =====

    /**
     * Get the artist's share of a given amount based on their revenue share percentage.
     */
    public function getArtistShareAttribute($totalAmount)
    {
        return round($totalAmount * ($this->revenue_share_percentage / 100), 10);
    }

    /**
     * Get the TeleMusic/platform fee for a given amount.
     */
    public function getTeleMusicFeeAttribute($totalAmount)
    {
        $teleMusicPercentage = 100 - $this->revenue_share_percentage;

        return round($totalAmount * ($teleMusicPercentage / 100), 10);
    }

    /**
     * Get the TeleMusic/platform fee percentage.
     */
    public function getTeleMusicFeePercentageAttribute()
    {
        return 100 - $this->revenue_share_percentage;
    }
}
