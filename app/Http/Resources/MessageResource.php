<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => (int) $this->group_id,
            'sender_id' => (int) $this->sender_id,
            'text' => $this->text,
            'file' => $this->file ? asset($this->file) : null,
            'created_at' => $this->created_at?->diffForHumans(),
            'created_at_iso' => $this->created_at?->toISOString(),

            'sender' => [
                'id' => $this->sender->id ?? null,
                'first_name' => $this->sender->first_name ?? null,
                'last_name' => $this->sender->last_name ?? null,
                'avatar' => isset($this->sender->avatar) && $this->sender->avatar ?
                    asset($this->sender->avatar) : asset('default/default_image.jpg'),
            ],

            'group' => [
                'id' => $this->group->id ?? null,
                'name' => $this->group->name ?? null,
                'avatar' => isset($this->group->avatar) && $this->group->avatar ?
                    asset($this->group->avatar) : asset('default/default_image.jpg'),
            ],
        ];
    }
}
