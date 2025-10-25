<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'avatar'        => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            'is_admin'      => (bool) $this->is_admin,
            'member_count'  => $this->member_count,
            'created_at'    => $this->created_at?->diffForHumans(),
            'updated_at'    => $this->updated_at?->diffForHumans(),

            // Creator details
            'creator' => [
                'id'         => $this->creator?->id,
                'name'       => $this->creator?->first_name . ' ' . $this->creator?->last_name,
                'email'      => $this->creator?->email,
                'avatar'     => $this->creator?->avatar ? asset($this->creator->avatar) : asset('default/default_person.jpg'),
            ],

            // Members
            'members' => $this->members->map(function ($member) {
                return [
                    'id'         => $member->id,
                    'role'       => $member->role,
                    'joined_at'  => $member->joined_at?->diffForHumans(),
                    'user'       => [
                        'id'              => $member->user?->id,
                        'name'            => $member->user->first_name . ' ' . $member->user?->last_name,
                        'email'           => $member->user?->email,
                        'avatar'          => $member->user?->avatar ? asset($member->user->avatar) : asset('default/default_person.jpg'),
                        'last_activity_at' => $member->user?->last_activity_at,
                    ]
                ];
            }),
        ];
    }
}
