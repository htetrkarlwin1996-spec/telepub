<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWithdrawalRequest extends Notification
{
    use Queueable;

    public function __construct(private readonly Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'withdrawal_requested',
            'title' => 'New withdrawal request',
            'message' => $this->withdrawal->artist->artist_name.' requested '.money($this->withdrawal->amount).'.',
            'url' => route('admin.withdrawals'),
            'withdrawal_id' => $this->withdrawal->id,
            'artist_id' => $this->withdrawal->artist_id,
        ];
    }
}
