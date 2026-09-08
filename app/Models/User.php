<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'bio', 'avatar', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    public function artist()
    {
        return $this->hasOne(Artist::class);
    }

    public function albums()
    {
        return $this->hasManyThrough(Album::class, Artist::class, 'user_id', 'artist_id');
    }

    public function songs()
    {
        return $this->hasManyThrough(Song::class, Artist::class, 'user_id', 'artist_id');
    }

    public function royalties()
    {
        return $this->hasManyThrough(Royalty::class, Artist::class, 'user_id', 'artist_id');
    }

    public function payouts()
    {
        return $this->hasManyThrough(Payout::class, Artist::class, 'user_id', 'artist_id');
    }

    public function withdrawals()
    {
        return $this->hasManyThrough(Withdrawal::class, Artist::class, 'user_id', 'artist_id');
    }
}
