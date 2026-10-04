<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgePost extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'cover_image', 'is_published', 'sort_order', 'published_at', 'author_id'];

    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
