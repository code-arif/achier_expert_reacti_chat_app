<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => [
                'id' => $this->sender->id ?? null,
                'first_name' => $this->sender->first_name ?? null,
                'last_name' => $this->sender->last_name ?? null,
                'username' => $this->sender->username ?? null,
                'avatar' => $this->sender->avatar ?? null,
            ],
            'status' => $this->status,
            'sent_at' => $this->created_at?->diffForHumans(), // Human-readable time
            'accepted_at' => $this->accepted_at?->diffForHumans(),
            'declined_at' => $this->declined_at?->diffForHumans(),
        ];
    }
}
