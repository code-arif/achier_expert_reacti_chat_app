<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\MessageResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

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
            Log::info("Broadcasting to group members", [
                'group_id' => $group->id,
                'total_members' => $group->members->count()
            ]);

            foreach ($group->members as $member) {
                $channelName = "group-message.{$member->user_id}";
                $channels[] = new PrivateChannel($channelName);

                Log::info("Adding channel", [
                    'user_id' => $member->user_id,
                    'channel' => $channelName,
                    'is_sender' => $member->user_id == $this->message->sender_id
                ]);
            }
        }

        Log::info("Total channels", ['count' => count($channels), 'channels' => array_map(function ($ch) {
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
            'message' => new MessageResource($this->message, 'broadcast')
        ];
    }

    /**
     * The event's broadcast name.
     */

}
