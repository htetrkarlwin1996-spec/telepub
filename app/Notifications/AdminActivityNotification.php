<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $event,
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $url = null,
        private readonly array $details = [],
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[TeleMusic] '.$this->title)
            ->view('emails.admin-notification', $this->mailData());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url ?? route('admin.dashboard'),
            'details' => $this->details,
        ];
    }

    private function mailData(): array
    {
        return [
            'title' => $this->title,
            'messageText' => $this->message,
            'details' => $this->details,
            'actionUrl' => $this->url ?? route('admin.dashboard'),
            'actionText' => 'Open Admin Dashboard',
        ];
    }
}
