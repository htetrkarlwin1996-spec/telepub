<?php

namespace App\Notifications;

use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReleaseSubmitted extends Notification
{
    use Queueable;

    public function __construct(private readonly Album $album) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
