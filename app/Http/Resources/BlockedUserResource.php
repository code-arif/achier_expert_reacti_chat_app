<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedUserResource extends JsonResource
{
    public function toArray($request)
    {
        $firstName = $this->blockedUser->first_name ?? '';
        $lastName  = $this->blockedUser->last_name ?? '';

        // Build full name safely
        $fullName = trim("$firstName $lastName");
        if ($fullName === '') {
            $fullName = $this->blockedUser->username ?? 'Unknown User';
        }

        return [
            'id'            => $this->id,
            'block_user_id' => $this->block_user_id,
            'created_at'    => Carbon::parse($this->created_at)->diffForHumans(),
            'blocked_user'  => [
                'id'         => $this->blockedUser->id,
                'full_name'  => $fullName,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'username'   => $this->blockedUser->username,
                'avatar'     => $this->blockedUser->avatar,
            ],
        ];
    }
}
