<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'user'      => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
            ],
            'comment'   => $this->comment,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
