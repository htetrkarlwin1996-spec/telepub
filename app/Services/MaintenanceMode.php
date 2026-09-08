<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class MaintenanceMode
{
    private const FLAG_FILE = 'framework/admin-maintenance';

    public function active(): bool
    {
        if (! is_file($this->path())) {
            return false;
        }

        $endsAt = $this->endsAt();

        if ($endsAt && $endsAt->isPast()) {
            $this->disable();

            return false;
        }

        return true;
    }

    public function enable(int $durationSeconds = 3600): void
    {
        file_put_contents($this->path(), json_encode([
            'enabled_at' => now()->toIso8601String(),
            'ends_at' => now()->addSeconds($durationSeconds)->toIso8601String(),
        ], JSON_THROW_ON_ERROR), LOCK_EX);
    }

    public function endsAt(): ?CarbonImmutable
    {
        $data = $this->data();

        return isset($data['ends_at'])
            ? CarbonImmutable::parse($data['ends_at'])
            : null;
    }

    public function remainingSeconds(): int
    {
        $endsAt = $this->endsAt();

        return $endsAt ? max(0, (int) now()->diffInSeconds($endsAt, false)) : 0;
    }

    public function disable(): void
    {
        $path = $this->path();

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function path(): string
    {
        return storage_path(self::FLAG_FILE);
    }

    private function data(): array
    {
        if (! is_file($this->path())) {
            return [];
        }

        return json_decode(file_get_contents($this->path()), true) ?: [];
    }
}
