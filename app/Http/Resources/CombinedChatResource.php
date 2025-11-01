<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CombinedChatResource extends JsonResource
{
     public function toArray($request)
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'room_id' => $this->room_id ?? null,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'last_message' => $this->last_message,
            'last_message_time' => $this->last_message_time
                ? Carbon::parse($this->last_message_time)->diffForHumans(short: true)
                : null,
            'is_active' => $this->is_active ?? false,
            'member_count' => $this->member_count ?? null,
        ];
    }



    private function getShortTime($time)
    {
        if (!$time) return null;

        $carbonTime = Carbon::parse($time);

        // Return compact human-readable time
        $diff = $carbonTime->diffForHumans([
            'parts' => 1,     // show only 1 part (e.g., "3h" instead of "3 hours 2 minutes")
            'short' => true,  // use short format like "3h" instead of "3 hours ago"
        ]);

        // Remove "ago"/"from now" if you want even shorter
        return str_replace([' ago', ' from now'], '', $diff);
    }
}
