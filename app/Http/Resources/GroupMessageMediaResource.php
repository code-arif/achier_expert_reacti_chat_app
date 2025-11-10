<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMessageMediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'file_url'   => asset($this->file),
            'file_type'  => $this->getFileType(),
            'sent_at'    => $this->created_at->diffForHumans(), // Human readable
            'sender'     => [
                'id'            => $this->sender->id,
                'name'          => trim("{$this->sender->first_name} {$this->sender->last_name}"),
                'avatar'        => $this->sender->avatar ? asset($this->sender->avatar) : null,
                // 'is_online'     => $this->sender->isOnline(), // assuming you have this method
            ],
        ];
    }

    // Optional: Detect file type (image, video, etc.)
    protected function getFileType()
    {
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $videoExts = ['mp4', 'avi', 'mov', 'wmv', 'mkv', 'webm'];

        if (in_array($extension, $imageExts)) return 'image';
        if (in_array($extension, $videoExts)) return 'video';
        return 'file';
    }
}
