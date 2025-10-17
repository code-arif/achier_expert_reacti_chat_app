<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use tidy;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->first_name . ' '. $this->last_name,
            'username' => $this->username ?? null,
            'bio' => $this->bio ?? null,
            'phone' => $this->phone ?? null,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            'created_at' => $this->created_at ? $this->created_at->diffForHumans() : null
        ];
    }
}
