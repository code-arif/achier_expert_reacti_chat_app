<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'comment'   => $this->comment,
            'rating'    => $this->rating,
            'created_at' => $this->created_at->toDateTimeString(),
            'user' => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
            ],
        ];
    }
}
