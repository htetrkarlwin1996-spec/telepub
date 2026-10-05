<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['email', 'otp', 'type', 'failed_attempts', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'failed_attempts' => 'integer',
        ];
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function recordFailedAttempt(int $maximum = 5): void
    {
        $attempts = $this->failed_attempts + 1;
        $this->forceFill([
            'failed_attempts' => $attempts,
            'used_at' => $attempts >= $maximum ? now() : null,
        ])->save();
    }
}
