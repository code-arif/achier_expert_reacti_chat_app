<?php

namespace App\Http\Resources\Chat\V2;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CombinedChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Handle array and object
        $data = is_array($this->resource) ? (object) $this->resource : $this->resource;

        return [
            'type' => $data->type,
            'id' => $data->id,
            'room_id' => $data->room_id ?? null,
            'name' => $data->name,
            'avatar' => $data->avatar,

            // Last message handling
            'last_message' => $this->formatLastMessage($data),
            'last_message_time' => $data->last_message_time
                ? Carbon::parse($data->last_message_time)->diffForHumans(['short' => true])
                : null,

            // Status info
            'is_active' => $data->is_active ?? false,
            'member_count' => $data->member_count ?? null,
            'unread_count' => $data->unread_count ?? 0,
        ];
    }

    /**
     * Format last message based on type
     */
    private function formatLastMessage($data): ?string
    {
        // If there's text, return it
        if ($data->last_message && $data->last_message !== '') {
            return $data->last_message;
        }

        // If there's a file, return appropriate icon/text
        if ($data->last_message_file ?? null) {
            return $this->getFileTypeText($data->last_message_file);
        }

        return null;
    }

    /**
     * Get appropriate text for file types
     */
    private function getFileTypeText($file): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'heic', 'heif'];
        if (in_array($extension, $imageExtensions)) {
            return '📷 Photo';
        }

        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg'];
        if (in_array($extension, $videoExtensions)) {
            return '🎥 Video';
        }

        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        if (in_array($extension, $audioExtensions)) {
            return '🎵 Audio';
        }

        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        if (in_array($extension, $documentExtensions)) {
            return '📄 Document';
        }

        return '📎 File';
    }
}
