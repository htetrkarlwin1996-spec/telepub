<?php

namespace App\Notifications;

use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReleaseSubmitted extends Notification
{
    use Queueable;

    public function __construct(private readonly Album $album) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[TeleMusic] New release submitted')
            ->view('emails.admin-notification', [
                'title' => 'New release submitted',
                'messageText' => $this->album->artist->artist_name.' submitted a release for review.',
                'details' => [
                    'Artist' => $this->album->artist->artist_name,
                    'Release' => $this->album->title,
                    'Type' => ucfirst($this->album->release_type),
                    'Tracks' => $this->album->songs()->count(),
                    'Status' => strtoupper($this->album->status),
                ],
                'actionUrl' => route('admin.releases.show', $this->album),
                'actionText' => 'Review Release',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'release_submitted',
            'title' => 'New release submitted',
            'message' => $this->album->artist->artist_name.' submitted “'.$this->album->title.'” for review.',
            'url' => route('admin.releases.show', $this->album),
            'album_id' => $this->album->id,
            'artist_id' => $this->album->artist_id,
        ];
    }
}
