<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\GroupMessageUserStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        // Current authenticated user
        $userId = auth('api')->id();

        // Fetch per-user view/blur status
        $status = GroupMessageUserStatus::where('message_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        return [
            'id' => $this->id,
            'group_id' => (int) $this->group_id,
            'sender_id' => (int) $this->sender_id,
            'text' => $this->text,
            'file' => $this->file ? asset($this->file) : null,
            'status'          => $this->status,

            // These now come from group_message_user_statuses table
            'is_blurred'    => $status->is_blurred ?? true,
            'is_viewed'     => $status->is_viewed ?? false,

            'message_type'    => $this->message_type ?? 'normal',
            'created_at' => $this->created_at?->diffForHumans(),


            // Media Type Detection
            'media_type' => $this->getMediaType(),

            'sender' => [
                'id' => $this->sender->id ?? null,
                'first_name' => $this->sender->first_name ?? null,
                'last_name' => $this->sender->last_name ?? null,
                'avatar' => isset($this->sender->avatar) && $this->sender->avatar ?
                    asset($this->sender->avatar) : asset('default/default_image.jpg'),
            ],

            'group' => [
                'id' => $this->group->id ?? null,
                'name' => $this->group->name ?? null,
                'avatar' => isset($this->group->avatar) && $this->group->avatar ?
                    asset($this->group->avatar) : asset('default/default_image.jpg'),
            ],
        ];
    }


    /**
     * Detect media type from file extension
     */
    private function getMediaType(): ?string
    {
        if (!$this->file) {
            return null;
        }

        // Extract file extension
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        // Image types
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'heif', 'heic', 'tiff', 'raw'];
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        }

        // Video types
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg', 'mpg'];
        if (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        // Audio types
        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        if (in_array($extension, $audioExtensions)) {
            return 'audio';
        }

        // Document types
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        if (in_array($extension, $documentExtensions)) {
            return 'document';
        }

        // Archive types
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];
        if (in_array($extension, $archiveExtensions)) {
            return 'archive';
        }

        // Default for unknown types
        return 'file';
    }
}
