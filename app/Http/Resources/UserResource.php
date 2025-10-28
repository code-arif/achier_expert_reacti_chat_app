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
            'last_name' => $this->last_name ?? null,
            'username' => $this->username ?? null,
            'bio' => $this->bio ?? null,
            'phone' => $this->phone ?? null,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),



            // Or use count aggregation (more efficient)
            'total_friends' => $this->friends_count ?? 0,

            // Count of groups
            // 'total_groups'  => $this->whenLoaded('groups', fn() => $this->groups->count()),
            // Or use count aggregation
            'total_groups'  => $this->groups_count ?? 0,
            'created_at' => $this->created_at ? $this->created_at->diffForHumans() : null
        ];
    }
}
