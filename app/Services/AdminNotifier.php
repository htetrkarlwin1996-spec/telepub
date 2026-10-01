<?php

namespace App\Services;

use App\Models\Album;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\NewWithdrawalRequest;
use App\Notifications\ReleaseSubmitted;
use Illuminate\Support\Facades\Notification;

class AdminNotifier
{
    public function withdrawalRequested(Withdrawal $withdrawal): void
    {
        $this->send(new NewWithdrawalRequest($withdrawal->loadMissing('artist')));
    }

    public function releaseSubmitted(Album $album): void
    {
        $this->send(new ReleaseSubmitted($album->loadMissing('artist')));
    }

    private function admins()
    {
        return User::where('role', 'admin')->where('is_active', true)->get();
    }

    private function send(object $notification): void
    {
        try {
            Notification::send($this->admins(), $notification);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
