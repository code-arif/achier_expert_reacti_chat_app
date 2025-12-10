<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use App\Http\Resources\ChatResource;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSendEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $payload;

    public function __construct($chat)
    {
        // Convert ChatResource → clean array
        $resource = new ChatResource($chat);

        // Convert to array manually
        $this->payload = $resource->resolve();
    }

    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel("chat-room.{$this->chat->room_id}"),
    //         new PrivateChannel("chat-receiver.{$this->chat->receiver_id}"),
    //         new PrivateChannel("chat-sender.{$this->chat->sender_id}")
    //     ];
    // }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat-room.{$this->payload['room_id']}"),
            new PrivateChannel("chat-receiver.{$this->payload['receiver_id']}"),
            new PrivateChannel("chat-sender.{$this->payload['sender_id']}")
        ];
    }

    //
    // public function broadcastWith(): array
    // {
    //     return [
    //         'chat' => new ChatResource($this->chat),
    //     ];
    // }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->payload
        ];
    }
}
