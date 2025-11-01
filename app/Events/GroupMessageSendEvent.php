<?php

namespace App\Events;

use App\Models\GroupMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GroupMessageSendEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
        Log::info("Broadcasting group message event", [
            'message_id' => $this->message->id,
            'group_id' => $this->message->group_id,
            'sender_id' => $this->message->sender_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcast to all group members privately
     */
    public function broadcastOn(): array
    {
        $channels = [];
        $group = $this->message->group()->with('members')->first();

        if ($group) {
            Log::info("📡 Broadcasting to group members", [
                'group_id' => $group->id,
                'total_members' => $group->members->count()
            ]);

            foreach ($group->members as $member) {
                $channelName = "group-message.{$member->user_id}";
                $channels[] = new PrivateChannel($channelName);

                Log::info("✅ Adding channel", [
                    'user_id' => $member->user_id,
                    'channel' => $channelName,
                    'is_sender' => $member->user_id == $this->message->sender_id
                ]);
            }
        }

        Log::info("📤 Total channels", ['count' => count($channels), 'channels' => array_map(function ($ch) {
            return $ch->name;
        }, $channels)]);

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'group_id' => $this->message->group_id,
                'sender_id' => $this->message->sender_id,
                'text' => $this->message->text,
                'file' => $this->message->file ? url($this->message->file) : null,
                'created_at' => $this->message->created_at->toISOString(),
                'sender' => [
                    'id' => $this->message->sender->id,
                    'first_name' => $this->message->sender->first_name,
                    'last_name' => $this->message->sender->last_name,
                    'avatar' => $this->message->sender->avatar ? url($this->message->sender->avatar) : null,
                ],
                'group' => [
                    'id' => $this->message->group->id,
                    'name' => $this->message->group->name,
                    'avatar' => $this->message->group->avatar ? url($this->message->group->avatar) : null,
                ]
            ]
        ];
    }

    /**
     * The event's broadcast name.
     */

}
