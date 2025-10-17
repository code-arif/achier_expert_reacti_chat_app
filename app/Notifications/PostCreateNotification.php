<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PostCreateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;

    /**
     * Create a new notification instance.
     */
    public function __construct($post)
    {
        $this->post = $post;
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
            'post_id'     => $this->post->id,
            'message'      => 'Your post has been created successfully!',
            'created_at'   => now()->toDateTimeString(),
        ];
    }
}
