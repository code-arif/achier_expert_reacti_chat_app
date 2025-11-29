<?php

namespace App\Http\Resources\Group;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMessageFormatResource extends JsonResource
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
            'group_id' => (int) $this->group_id,
            'sender_id' => (int) $this->sender_id,
            'text' => $this->text,
            'file' => $this->file ? asset($this->file) : null,
            'status' => $this->status,
            'is_blurred' => (bool) $this->is_blurred,
            'is_viewed' => (bool) $this->is_viewed,
            'message_type' => $this->message_type ?? 'normal',
            'created_at' => $this->created_at?->diffForHumans(),

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

            // Adding statuses
            'message_status' => $this->messageStatus->map(function ($status) {
                return [
                    'id' => $status->id,
                    'message_id' => $status->message_id,
                    'user_id' => $status->user_id,
                    'is_viewed' => (bool) $status->is_viewed,
                    'is_blurred' => (bool) $status->is_blurred,
                    'created_at' => $status->created_at->diffForHumans(),
                    'updated_at' => $status->updated_at->diffForHumans,
                ];
            })
        ];
    }
}
