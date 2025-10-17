<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class EventCreateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // You can also add 'broadcast', 'mail', etc.
    }

    /**
     * Get the array representation of the notification (stored in DB).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id'     => $this->event->id,
            'event_title'  => $this->event->title,
            'banner'       => $this->event->banner,
            'message'      => 'Your event has been created successfully!',
            'created_at'   => now()->toDateTimeString(),
        ];
    }
}
