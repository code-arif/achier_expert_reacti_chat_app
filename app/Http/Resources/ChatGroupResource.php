<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->diffForHumans(short: true),
            'updated_at' => $this->updated_at->diffForHumans(short: true),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->diffForHumans(short: true) : null,

            // Last message with relative time
            'last_message' => $this->when(isset($this->last_message), function () {
                if (!$this->last_message) return null;

                return [
                    'id' => $this->last_message['id'] ?? null,
                    'text' => $this->last_message['text'] ?? null,
                    'file' => $this->last_message['file'] ?? null,
                    'created_at' => $this->last_message['created_at'] ?? null,
                    'relative_time' => $this->last_message['relative_time'] ?? null,
                ];
            }),

            'unread_count' => $this->unread_count ?? 0,
            'member_count' => $this->member_count ?? $this->members->count(),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'first_name' => $this->creator->first_name,
                    'last_name' => $this->creator->last_name,
                    'avatar' => $this->creator->avatar ? asset($this->creator->avatar) : asset('default/default_image.jpg'),
                ];
            }),



            'members' => $this->whenLoaded('members', function () {


                return $this->members->map(function ($member) {

                    $user = $member->user;

                    // Determine role: if user is group creator → owner
                    $role = $member->role ?? 'member'; // default from pivot
                    if ($user->id === $this->created_by) {
                        $role = 'owner'; // Force owner for creator
                    }

                    return [
                        'id' => $member->id,
                        'group_id' => $member->group_id,
                        'user_id' => $member->user_id,
                        'role' => $role,
                        'joined_at' => $member->created_at->diffForHumans(short: true),
                        'user' => [
                            'id' => $member->user->id,
                            'first_name' => $member->user->first_name,
                            'last_name' => $member->user->last_name,
                            'avatar' => $member->user->avatar ? asset($member->user->avatar) : asset('default/default_image.jpg'),
                            'last_activity_at' => $member->user->last_activity_at ? $member->user->last_activity_at->diffForHumans(short: true) : null,
                        ],
                    ];
                });
            }),
        ];
    }
}
