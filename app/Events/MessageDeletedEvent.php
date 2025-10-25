<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $chatId;
    public $roomId;
    public $deleteType;

    public function __construct($chatId, $roomId, $deleteType)
    {
        $this->chatId = $chatId;
        $this->roomId = $roomId;
        $this->deleteType = $deleteType;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat-room.' . $this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'MessageDeletedEvent';
    }
}
