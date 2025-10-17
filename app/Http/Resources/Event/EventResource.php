<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use App\Http\Resources\Comment\CommentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
         $authUser = auth('api')->user();

        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'start_date'     => $this->start_date?->toDateString(),
            'start_time'     => $this->start_time?->toTimeString(),
            'end_date'       => $this->end_date?->toDateString(),
            'end_time'       => $this->end_time?->toTimeString(),
            'time_zone'      => $this->time_zone,
            'all_day_event'  => $this->all_day_event,
            'is_my_event' => $authUser && $authUser->id === $this->user_id,
            'banner_url' => $this->banner ? url($this->banner) : null,

            'tags' => json_decode($this->tags) ?: [],


            'status'         => $this->status,
            'created_at'     => $this->created_at->toDateTimeString(),

            'like_count'     => $this->like_count,
            // Return limited users who liked (e.g. first 5)
            'likes' => $this->likes->map(function ($like) {
                return [
                    'id'     => $like->user->id,
                    'name'   => $like->user->f_name . ' ' . $like->user->l_name,
                    'avatar' => $like->user->avatar,
                ];
            })->take(5),

            'comment_count'  => $this->comment_count,
            'comments'       => CommentResource::collection($this->comments),

            'share_count'    => $this->share_count,

            // venue
            'venue' => [
                'venue_id'   => $this->venue_id,
                'venue_name' => $this->venue->title ?? null,
                'location' => $this->venue->location ?? null,
                'email' => $this->venue->email ?? null,
                'phone' => $this->venue->phone ?? null,
            ],

            //user info
            'user' => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
                'email' => $this->user->email ?? null,
            ],

            // ticket info
            'ticket' => [
                'id' => $this->ticket->id ?? null,
                'name' => $this->ticket->ticket_name ?? null,
                'description' => $this->ticket->description ?? null,
                'show_description'=> $this->ticket->show_description ?? null,
                'start_date' => $this->ticket->start_date ?? null,
                'start_time' => $this->ticket->start_time ?? null,
                'end_date' => $this->ticket->end_date ?? null,
                'end_time' => $this->ticket->end_time ?? null,
                'price' => $this->ticket->price ?? null,
                'capacity_type' => $this->ticket->capacity_type ?? null,
                'shared_capacity' => $this->ticket->shared_capacity ?? null,
                'independent_capacity' => $this->ticket->independent_capacity ?? null,
                'external_ticket_url' => $this->ticket->external_ticket_url ?? null,
                'sku' => $this->ticket->sku ?? null,
                'attendee_collection' => $this->ticket->attendee_collection ?? null,
            ]
        ];
    }

    // EventRequest.php
    public function passedValidation()
    {
        if ($this->has('tags')) {
            $this->merge([
                'tags' => array_unique($this->tags)
            ]);
        }
    }
}
