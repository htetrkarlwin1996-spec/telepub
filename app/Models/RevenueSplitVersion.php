<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueSplitVersion extends Model
{
    protected $fillable = ['album_id', 'version', 'splits', 'effective_from', 'locked_at', 'approved_by'];

    protected $casts = ['splits' => 'array', 'effective_from' => 'datetime', 'locked_at' => 'datetime'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
