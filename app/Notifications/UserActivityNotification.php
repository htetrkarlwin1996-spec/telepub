<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $url = null,
        private readonly string $actionText = 'Open TeleMusic',
        private readonly array $details = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[TeleMusic] '.$this->title)
            ->view('emails.user-notification', [
                'title' => $this->title,
                'messageText' => $this->message,
                'details' => $this->details,
                'actionUrl' => $this->url ?? route('dashboard'),
                'actionText' => $this->actionText,
            ]);
    }
}
