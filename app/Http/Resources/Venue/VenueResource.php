<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Request;
use App\Http\Resources\Review\ReviewResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'capacity'          => $this->capacity,
            'location'          => $this->location,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'service_start_time' => $this->service_start_time,
            'service_end_time'  => $this->service_end_time,
            'ticket_price'      => $this->ticket_price,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'status'            => (int) $this->status,
            'created_at'        => $this->created_at->toDateTimeString(),

            // Relations
            'detail' => [
                'description' => optional($this->detail)->description,
                'features'    => $this->detail->features ?? '',
            ],

            'media' => $this->media->map(function ($media) {
                return [
                    'id'        => $media->id,
                    'image_url' => asset($media->image_url),
                    'video_url' => asset($media->video_url),
                ];
            }),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),

            // Average rating
            'average_rating' => $this->reviews_avg_rating ? round($this->reviews_avg_rating, 1) : null,
            'reviews_count'=> $this->reviews_count ?? null
        ];
    }
}
