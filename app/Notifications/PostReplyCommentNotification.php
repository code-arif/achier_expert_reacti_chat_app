<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostReplyCommentNotification extends Notification
{
    use Queueable;

    protected $replyUser;
    protected $comment;
    protected $post;

    /**
     * Create a new notification instance.
     */
    public function __construct($replyUser, $comment, $post)
    {
        $this->replyUser = $replyUser;
        $this->comment = $comment;
        $this->post = $post;
    }


    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        $contentWords = explode(' ', strip_tags($this->post->content));
        $shortContent = implode(' ', array_slice($contentWords, 0, 3)) . (count($contentWords) > 3 ? '...' : '');

        return [
            'reply_user_id' => $this->replyUser->id,
            'reply_user_name' => $this->replyUser->name,
            'reply_user_avatar' => $this->replyUser->avatar,
            'message'           => "{$this->replyUser->name} replied to your comment on '{$shortContent}'",
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
