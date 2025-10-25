<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;
    public $roomId;
    public $userId;
    public $reaction;
    public $reactionCounts;

    public function __construct($chatId, $roomId, $userId, $reaction, $reactionCounts)
    {
        $this->chatId = $chatId;
        $this->roomId = $roomId;
        $this->userId = $userId;
        $this->reaction = $reaction;
        $this->reactionCounts = $reactionCounts;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-room.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageReactionEvent';
    }
}
