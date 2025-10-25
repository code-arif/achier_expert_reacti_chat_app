<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "avatar" => $this->avatar ? asset($this->avatar) : asset('default/default_image.jpg'),
            "created_at"=> $this->created_at?->diffForHumans(),
            "updated_at"=> $this->updated_at?->diffForHumans(),
            "deleted_at"=> $this->deleted_at?->diffForHumans(),
        ];
    }
}
