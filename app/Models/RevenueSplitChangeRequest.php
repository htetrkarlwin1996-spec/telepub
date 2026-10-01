<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueSplitChangeRequest extends Model
{
    protected $fillable = [
        'album_id', 'requested_by', 'current_splits', 'proposed_splits', 'reason', 'status',
        'reviewed_by', 'reviewed_at', 'admin_note',
    ];

    protected $casts = ['current_splits' => 'array', 'proposed_splits' => 'array', 'reviewed_at' => 'datetime'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
