<?php

namespace App\Http\Resources\Calender;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'all_day'     => (bool) $this->all_day,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'color_code'  => $this->color_code,
            'event_id'    => $this->event_id,
            'created_at'  => $this->created_at->toDateTimeString(),
            'event' => [
                'id'         => $this->event_id,
                'title'      => $this->event->title,
                'start_date' => $this->event->start_date->format('j M Y'),
                'start_time' => $this->event->start_time->format('h:i A'),
                'end_date'   => optional($this->event->end_date)->format('j M Y'),
                'end_time'   => $this->event->end_time->format('h:i A'),
                'venue'      => [
                    'id'       => $this->event->venue_id ?? null,
                    'location' => $this->event->venue->location ?? null,
                ]
            ],
        ];
    }
}
