<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Typing Event
class TypingEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $roomId;
    public $userId;
    public $userName;
    public $isTyping;

    public function __construct($roomId, $userId, $userName, $isTyping)
    {
        $this->roomId = $roomId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat-room.' . $this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'TypingEvent';
    }
}
