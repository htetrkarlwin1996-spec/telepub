<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewWithdrawalRequest extends Notification
{
    use Queueable;

    public function __construct(private readonly Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[TeleMusic] New withdrawal request')
            ->view('emails.admin-notification', [
                'title' => 'New withdrawal request',
                'messageText' => $this->withdrawal->artist->artist_name.' requested a withdrawal.',
                'details' => [
                    'Artist' => $this->withdrawal->artist->artist_name,
                    'Amount' => money($this->withdrawal->amount),
                    'Fee' => money($this->withdrawal->fee),
                    'Net payout' => money($this->withdrawal->total),
                    'Payment method' => ucwords(str_replace('_', ' ', $this->withdrawal->payment_method ?? 'Not specified')),
                    'Status' => strtoupper($this->withdrawal->status),
                ],
                'actionUrl' => route('admin.withdrawals'),
                'actionText' => 'Review Withdrawal',
            ]);
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
