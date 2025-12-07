<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserListResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($user) {
                return [
                    'id'                => $user->id,
                    'full_name'         => trim("{$user->first_name} {$user->last_name}"),
                    'username'          => $user->username,
                    'avatar'            => $user->avatar ? url($user->avatar) : null,
                    'is_friend'         => $user->is_friend ?? false,
                    'is_request_sent'   => $user->is_request_sent ?? false,
                ];
            }),

            'pagination' => [
                'total'        => $this->total(),
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'per_page'     => $this->perPage(),
            ],
        ];
    }
}
