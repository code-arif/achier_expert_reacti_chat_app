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
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name ?? null,
            'username' => $this->username ?? null,
            'email' => $this->email ?? null,
            'bio' => $this->bio ?? null,
            'phone' => $this->phone ?? null,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            'total_friends' => $this->friends_count ?? 0,
            'total_groups'  => $this->groups_count ?? 0,
            'created_at' => $this->created_at ? $this->created_at->diffForHumans() : null
        ];
    }
}
