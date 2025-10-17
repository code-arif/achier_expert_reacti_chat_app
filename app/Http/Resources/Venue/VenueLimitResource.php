<?php

namespace App\Http\Resources\Venue;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueLimitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'description' => $this->detail && $this->detail->description
                ? Str::limit($this->detail->description, 100) // optional truncate
                : null,
            'reviews_avg_rating' => $this->reviews_avg_rating
                ? number_format($this->reviews_avg_rating, 1)
                : null,
            'media' => $this->media->isNotEmpty()
                ? [
                    'id'       => $this->media->first()->id,
                    'venue_id' => $this->media->first()->venue_id,
                    'image_url' => asset($this->media->first()->image_url),
                ]
                : null,
        ];
    }
}
