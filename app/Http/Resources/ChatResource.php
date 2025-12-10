<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    private function safe($value)
    {
        if ($value === null) return null;

        if (is_string($value)) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                return utf8_encode($value);
            }
            return $value;
        }

        return $value;
    }

    public function toArray($request): array
    {
        $authId = auth('api')->id();
        $isSent = $this->sender_id == $authId;

        // Safe short text
        $shortText = '';
        if ($this->text) {
            $shortText = mb_strlen($this->text) > 30
                ? mb_substr($this->text, 0, 27) . '...'
                : $this->text;
        } elseif ($this->file) {
            $shortText = 'Media';
        }

        return [
            'id'              => $this->id,
            'sender_id'       => $this->sender_id,
            'receiver_id'     => $this->receiver_id,
            'text'            => $this->safe($this->text ?? ''),
            'file'            => $this->file ? $this->safe(asset($this->file)) : null,
            'room_id'         => $this->room_id,
            'status'          => $this->status,
            'is_blurred'      => (bool) $this->is_blurred,
            'is_viewed'       => (bool) $this->is_viewed,
            'message_type'    => $this->message_type ?? 'normal',
            'media_type'      => $this->getMediaType(),
            'humanize_date'   => $this->created_at ? $this->safe($this->created_at->diffForHumans()) : 'just now',
            'short_text'      => $this->safe($shortText),
            'type'            => $isSent ? 'sent' : 'received',

            'sender' => [
                'id'              => $this->sender->id,
                'first_name'      => $this->safe($this->sender->first_name),
                'last_name'       => $this->safe($this->sender->last_name),
                'avatar'          => $this->safe(
                    $this->sender->avatar
                        ? asset($this->sender->avatar)
                        : asset('default/default_image.jpg')
                ),
                'last_activity_at' => $this->safe($this->sender->last_activity_at ?? ''),
            ],

            'receiver' => [
                'id'              => $this->receiver->id,
                'first_name'      => $this->safe($this->receiver->first_name),
                'last_name'       => $this->safe($this->receiver->last_name),
                'avatar'          => $this->safe(
                    $this->receiver->avatar
                        ? asset($this->receiver->avatar)
                        : asset('default/default_image.jpg')
                ),
                'last_activity_at' => $this->safe($this->receiver->last_activity_at ?? ''),
            ],

            'room' => [
                'id'           => $this->room->id,
                'user_one_id'  => $this->room->user_one_id,
                'user_two_id'  => $this->room->user_two_id,
            ],
        ];
    }

    private function getMediaType(): ?string
    {
        if (!$this->file) return null;

        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        $image = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'];
        $video = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg', 'mpg'];
        $audio = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        $doc   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        $zip   = ['zip', 'rar', '7z', 'tar', 'gz'];

        return match (true) {
            in_array($extension, $image) => 'image',
            in_array($extension, $video) => 'video',
            in_array($extension, $audio) => 'audio',
            in_array($extension, $doc)   => 'document',
            in_array($extension, $zip)   => 'archive',
            default => 'file'
        };
    }
}
