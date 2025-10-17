<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'user'    => [
                'id'   => $this->user->id,
                'name' => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
            ],
            'rating'  => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
