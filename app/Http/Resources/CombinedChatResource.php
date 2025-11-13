<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CombinedChatResource extends JsonResource
{
    public function toArray($request)
    {
        // Handle array and object
        $data = is_array($this->resource) ? (object) $this->resource : $this->resource;

        return [
            'type' => $data->type,
            'id' => $data->id,
            'room_id' => $data->room_id ?? null,
            'name' => $data->name,
            'avatar' => $data->avatar,
            'last_message' => ($data->last_message && $data->last_message !== '')
                ? $data->last_message
                : (($data->last_message_file ?? null)
                    ? '📎 File attachment'
                    : null),
            'last_message_time' => $data->last_message_time
                ? Carbon::parse($data->last_message_time)->diffForHumans(short: true)
                : null,
            'is_active' => $data->is_active ?? false,
            'member_count' => $data->member_count ?? null,
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
