<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventReplyCommentNotification extends Notification
{
    use Queueable;

    protected $replyUser;
    protected $comment;
    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct($replyUser, $comment, $event)
    {
        $this->replyUser = $replyUser;
        $this->comment = $comment;
        $this->event = $event;
    }


    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        // Get first 2–3 words of the event title
        $titleWords = explode(' ', strip_tags($this->event->title));
        $shortTitle = implode(' ', array_slice($titleWords, 0, 3)) . (count($titleWords) > 3 ? '...' : '');

        return [
            'reply_user_id' => $this->replyUser->id,
            'reply_user_name' => $this->replyUser->name,
            'reply_user_avatar' => $this->replyUser->avatar,
            'message'           => "{$this->replyUser->name} replied to your comment on '{$shortTitle}'",
            'event_id' => $this->event->id,
            'comment_id' => $this->comment->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
