<?php

namespace App\Http\Resources;

use App\Models\Friend;
use Illuminate\Http\Resources\Json\JsonResource;

class FindUserResource extends JsonResource
{
    public function toArray($request)
    {
        $authUser = auth('api')->user();

        $isFriend = Friend::where(function ($q) use ($authUser) {
            $q->where('user_id', $authUser->id)
                ->where('friend_id', $this->id);
        })->exists();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? url($this->avatar) : null,
            'is_friend' => $isFriend,
        ];
    }
}
