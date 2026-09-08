<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MusicStore extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo', 'description', 'url', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'store_id');
    }

    public function royalties()
    {
        return $this->hasMany(Royalty::class, 'store_id');
    }
}
