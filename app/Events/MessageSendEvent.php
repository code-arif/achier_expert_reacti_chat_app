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

    // public function __construct($data)
    // {
    //     $this->data = $data;

    //     Log::info("Broadcasting message event", ['chat' => $this->data]);
    // }

    public function __construct($chat)
    {
        $this->chat = $chat;

        Log::info("Broadcasting message event", [
            'chat' => new ChatResource($chat)
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat-room.{$this->chat->room_id}"),
            new PrivateChannel("chat-receiver.{$this->chat->receiver_id}"),
            new PrivateChannel("chat-sender.{$this->chat->sender_id}")
        ];
    }

    //
    public function broadcastWith(): array
    {
        return [
            'chat' => new ChatResource($this->chat),
        ];
    }
}
