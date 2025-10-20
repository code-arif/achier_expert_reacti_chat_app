<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'username'  => $this->username,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
        ];
    }
}
