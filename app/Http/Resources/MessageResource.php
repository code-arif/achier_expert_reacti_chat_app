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
            'media_type' => $this->resolveMediaType($this->file),

            // Reply block
            'reply_to' => $this->whenLoaded('replyTo', function () {
                $replied = $this->replyTo;

                if (!$replied) return null;

                return [
                    'id'         => $replied->id,
                    'sender_id'  => (int) $replied->sender_id,
                    'text'       => $replied->text,
                    'file'       => $replied->file ? asset($replied->file) : null,
                    'media_type' => $this->resolveMediaType($replied->file),
                    'sender'     => [
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
    protected function resolveMediaType(?string $file): ?string
    {
        if (!$file) return null;

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $imageExtensions    = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'heif', 'heic', 'tiff', 'raw'];
        $videoExtensions    = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg', 'mpg'];
        $audioExtensions    = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        $archiveExtensions  = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (in_array($extension, $imageExtensions))    return 'image';
        if (in_array($extension, $videoExtensions))    return 'video';
        if (in_array($extension, $audioExtensions))    return 'audio';
        if (in_array($extension, $documentExtensions)) return 'document';
        if (in_array($extension, $archiveExtensions))  return 'archive';

        return 'file';
    }
}
