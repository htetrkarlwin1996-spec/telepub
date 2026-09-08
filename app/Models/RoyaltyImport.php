<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoyaltyImport extends Model
{
    protected $fillable = ['file_name', 'file_hash', 'row_count', 'entered_by'];

    public function royalties()
    {
        return $this->hasMany(Royalty::class);
    }
}
