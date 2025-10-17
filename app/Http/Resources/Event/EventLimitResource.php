<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventLimitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_date'   => $this->end_date,
            'end_time'   => $this->end_time,
            'banner'     => $this->banner ? asset($this->banner) : null,

            'venue' => [
                'id'       => $this->venue->id ?? null,
                'title'    => $this->venue->title ?? null,
                'location' => $this->venue->location ?? null,
            ],
        ];
    }
}
