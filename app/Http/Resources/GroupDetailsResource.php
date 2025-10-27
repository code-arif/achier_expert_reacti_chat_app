<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            'is_admin' => (bool) ($this->is_admin ?? false),
            'member_count' => $this->member_count ?? $this->members->count(),
            'created_at' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->diffForHumans(),

            'creator' => [
                'id' => $this->creator?->id,
                'first_name' => $this->creator?->first_name,
                'last_name' => $this->creator?->last_name,
                'email' => $this->creator?->email,
                'avatar' => $this->creator?->avatar ? asset($this->creator->avatar) : asset('default/default_image.jpg'),
            ],

            'members' => $this->members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'role' => $member->role,
                    'joined_at' => $member->created_at?->diffForHumans(),
                    'user' => [
                        'id' => $member->user?->id,
                        'first_name' => $member->user?->first_name,
                        'last_name' => $member->user?->last_name,
                        'email' => $member->user?->email,
                        'avatar' => $member->user?->avatar ? asset($member->user->avatar) : asset('default/default_image.jpg'),
                        'last_activity_at' => $member->user?->last_activity_at?->diffForHumans(),
                    ]
                ];
            }),
        ];
    }
}
