<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedUserResource extends JsonResource
{
    public function toArray($request)
    {
        $firstName = $this->reportedUser->first_name ?? '';
        $lastName  = $this->reportedUser->last_name ?? '';

        $fullName = trim("$firstName $lastName");
        if ($fullName === '') {
            $fullName = $this->reportedUser->username ?? 'Unknown User';
        }

        return [
            'id'               => $this->id,
            'reported_user_id' => $this->reported_user_id,
            'reason'           => $this->reason,
            'description'      => $this->description,
            'created_at'       => Carbon::parse($this->created_at)->diffForHumans(),
            'reported_user'    => [
                'id'         => $this->reportedUser->id,
                'full_name'  => $fullName,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'username'   => $this->reportedUser->username,
                'avatar'     => $this->reportedUser->avatar,
            ],
        ];
    }
}
