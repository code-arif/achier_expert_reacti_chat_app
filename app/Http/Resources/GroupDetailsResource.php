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
                $user = $member->user;

                // Determine role: if user is group creator → owner
                $role = $member->role ?? 'member'; // default from pivot
                if ($user->id === $this->created_by) {
                    $role = 'owner'; // Force owner for creator
                }

                return [
                    // 'id' => $member->id,
                    'role' => $role, // owner / admin / member
                    'joined_at' => $member->created_at?->diffForHumans(),
                    'user' => [
                        'id' => $user?->id,
                        'first_name' => $user?->first_name,
                        'last_name' => $user?->last_name,
                        'email' => $user?->email,
                        'avatar' => $user?->avatar ? asset($user->avatar) : asset('default/default_image.jpg'),
                        'last_activity_at' => $user?->last_activity_at?->diffForHumans(),
                    ]
                ];
            }),
        ];
    }
}
