<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $roomId;
    public $updateType;
    public $data;

    public function __construct($roomId, $updateType, $data = [])
    {
        $this->roomId = $roomId;
        $this->updateType = $updateType;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat-room.' . $this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'GroupUpdatedEvent';
    }
}
