<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class FriendRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authId = auth('api')->id();
        $person = $this->sender_id === $authId ? $this->receiver : $this->sender;

        return [
            'id' => $this->id,
            'person' => [
                'id'         => $person->id ?? null,
                'first_name' => $person->first_name ?? null,
                'last_name'  => $person->last_name ?? null,
                'username'   => $person->username ?? null,
                'avatar'     => $person?->avatar ? asset($person->avatar) : asset('default/default_image.jpg'),
                'full_name'  => trim("{$person->first_name} {$person->last_name}")
                    ?: ($person->username ?? 'Unknown'),
            ],
            'status'       => $this->status,
            'sent_at'      => $this->created_at?->diffForHumans(),
            'accepted_at'  => $this->accepted_at?->diffForHumans(),
            'declined_at'  => $this->declined_at?->diffForHumans(),
            'is_sent'      => $this->sender_id === $authId,
        ];
    }
}
