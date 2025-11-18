<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        // Determine if this message is sent by current user
        $authId = auth('api')->id();
        $isSent = $this->sender_id == $authId;

        return [
            'id'              => $this->id,
            'sender_id'       => $this->sender_id,
            'receiver_id'     => $this->receiver_id,
            'text'            => $this->text ?? '',
            'file'            => $this->file ? asset('' . $this->file) : null,
            'room_id'         => $this->room_id,
            'status'          => $this->status,
            'is_blurred'      => (bool) $this->is_blurred,
            'is_viewed'       => (bool) $this->is_viewed,
            'message_type'    => $this->message_type ?? 'normal',
            'media_type'      => $this->getMediaType(),           // ← New field added
            'humanize_date'   => $this->created_at?->diffForHumans() ?? 'just now',
            'short_text'      => $this->text ? (strlen($this->text) > 30
                ? substr($this->text, 0, 27) . '...'
                : $this->text) : ($this->file ? 'Media' : ''),

            'type'            => $isSent ? 'sent' : 'received',

            'sender' => [
                'id'              => $this->sender->id,
                'first_name'      => $this->sender->first_name,
                'last_name'       => $this->sender->last_name,
                'avatar'          => $this->sender->avatar
                    ? asset('' . $this->sender->avatar)
                    : asset('default/default_image.jpg'),
                'last_activity_at' => $this->sender->last_activity_at,
            ],

            'receiver' => [
                'id'              => $this->receiver->id,
                'first_name'      => $this->receiver->first_name,
                'last_name'       => $this->receiver->last_name,
                'avatar'          => $this->receiver->avatar
                    ? asset('' . $this->receiver->avatar)
                    : asset('default/default_image.jpg'),
                'last_activity_at' => $this->receiver->last_activity_at,
            ],

            'room' => [
                'id'           => $this->room->id,
                'user_one_id'  => $this->room->user_one_id,
                'user_two_id'  => $this->room->user_two_id,
            ],
        ];
    }

    // Tomr exact same logic copy kora hoise
    private function getMediaType(): ?string
    {
        if (!$this->file) {
            return null;
        }

        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'];
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        }

        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg', 'mpg'];
        if (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        if (in_array($extension, $audioExtensions)) {
            return 'audio';
        }

        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        if (in_array($extension, $documentExtensions)) {
            return 'document';
        }

        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];
        if (in_array($extension, $archiveExtensions)) {
            return 'archive';
        }

        return 'file';
    }
}
