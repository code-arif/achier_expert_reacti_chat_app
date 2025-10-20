<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blocked_user' => [
                'id' => $this->blockedUser->id ?? null,
                'first_name' => $this->blockedUser->first_name ?? null,
                'last_name' => $this->blockedUser->last_name ?? null,
                'username' => $this->blockedUser->username ?? null,
                'avatar' => $this->blockedUser->avatar ?? null,
            ],
            'reason' => $this->reason,
            'description' => $this->description,
            'blocked_at' => $this->created_at?->diffForHumans(), // formatted time
        ];
    }
}
