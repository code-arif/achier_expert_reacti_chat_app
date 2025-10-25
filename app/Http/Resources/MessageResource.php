<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => (string) $this->group_id,
            'sender_id' => (int) $this->sender_id,
            'text' => $this->text,
            'file' => $this->file ? url($this->file) : null,
            'created_at' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->diffForHumans(),

            'sender' => [
                'id' => $this->sender->id ?? null,
                'first_name' => $this->sender->first_name ?? null,
                'last_name' => $this->sender->last_name ?? null,
                'avatar' => isset($this->sender->avatar) ? url($this->sender->avatar) : null,
                'last_activity_at' => $this->sender->last_activity_at?->diffForHumans(),
            ],

            'group' => [
                'id' => $this->group->id ?? null,
                'name' => $this->group->name ?? null,
                'avatar' => isset($this->group->avatar) ? url($this->group->avatar) : null,
            ],
        ];
    }
}
