<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowNotification extends Notification
{
    use Queueable;

    protected $authUser;

    /**
     * Create a new notification instance.
     */
    public function __construct($authUser)
    {
        $this->authUser = $authUser;
    }

    /**
     * Get the notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification (stored in DB).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'follower_id'     => $this->authUser->id,
            'follower_name'   => $this->authUser->f_name . ' ' . $this->authUser->l_name,
            'follower_avatar' => $this->authUser->avatar,
            'message'         => "{$this->authUser->f_name} started following you.",
            'created_at'      => now()->toDateTimeString(),
        ];
    }
}
