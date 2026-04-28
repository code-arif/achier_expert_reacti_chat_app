<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'room_id' => $this->room_id,
            'text' => $this->text,
            'file' => $this->file,
            'status' => $this->status,
            'is_blurred' => $this->is_blurred,
            'is_viewed' => $this->is_viewed,
            'message_type' => $this->message_type,
            'is_my_text' => $this->is_my_text ?? false,
            'should_show_blur' => $this->should_show_blur ?? false,
            'humanize_date' => $this->created_at->diffForHumans(),
            // 'short_text' => $this->text ? (strlen($this->text) > 20 ? substr($this->text, 0, 20) . '...' : $this->text) : null,
            'short_text' => $this->text
                ? (mb_strlen($this->text, 'UTF-8') > 20
                    ? mb_substr($this->text, 0, 20, 'UTF-8') . '...'
                    : $this->text)
                : null,
            'type' => $this->is_my_text ? 'sent' : 'received',

            // Media Type Detection
            'media_type' => $this->getMediaType(),

            // Reply block
            'reply_to' => $this->whenLoaded('replyTo', function () {
                $replied = $this->replyTo;

                if (!$replied) return null;

                $mediaType = null;
                if ($replied->file) {
                    $ext = strtolower(pathinfo($replied->file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))     $mediaType = 'image';
                    elseif (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm']))        $mediaType = 'video';
                    elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'aac', 'm4a']))         $mediaType = 'audio';
                    elseif (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'])) $mediaType = 'document';
                    else $mediaType = 'file';
                }

                return [
                    'id'                => $replied->id,
                    'sender_id'         => (int) $replied->sender_id,
                    'text'              => $replied->text,
                    'file'              => $replied->file ? asset($replied->file) : null,
                    'media_type'        => $mediaType,

                    // replied message's own blur state — NOT derived from parent
                    'is_blurred'        => (bool) $replied->is_blurred,

                    'parent_message_id' => $replied->reply_to_id,
                    'parent_message'    => $replied->parentReply ? [
                        'id'   => $replied->parentReply->id,
                        'text' => $replied->parentReply->text,
                        'file' => $replied->parentReply->file ? asset($replied->parentReply->file) : null,
                    ] : null,
                    'sender' => [
                        'id'         => $replied->sender->id ?? null,
                        'first_name' => $replied->sender->first_name ?? null,
                        'last_name'  => $replied->sender->last_name ?? null,
                        'avatar'     => isset($replied->sender->avatar) && $replied->sender->avatar
                            ? asset($replied->sender->avatar)
                            : asset('default/default_image.jpg'),
                    ],
                ];
            }),

            'sender' => [
                'id' => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'last_name' => $this->sender->last_name,
                'avatar' => $this->sender->avatar,
                'last_activity_at' => $this->sender->last_activity_at,
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'first_name' => $this->receiver->first_name,
                'last_name' => $this->receiver->last_name,
                'avatar' => $this->receiver->avatar,
                'last_activity_at' => $this->receiver->last_activity_at,
            ],
            'room' => [
                'id' => $this->room->id,
                'user_one_id' => $this->room->user_one_id,
                'user_two_id' => $this->room->user_two_id,
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
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'heic', 'tiff', 'psd', 'raw', 'ai', 'heif',];
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
