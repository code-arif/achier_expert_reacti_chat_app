<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\GroupMessageUserStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    protected $type;


    public function __construct($resource, $type = null)
    {
        parent::__construct($resource);
        $this->type = $type;
    }
    public function toArray(Request $request, $type = null): array
    {
        $userId = auth('api')->id();

        // FIX #4: Preloaded relation use করো — fresh DB query নয়
        // messageStatus eager loaded হলে সেখান থেকে নাও (N+1 avoid)
        // messageStatus load করার সময় user_id filter করা ছিল, তাই first() ই সেই user-এরটা
        if ($this->relationLoaded('messageStatus')) {
            $status = $this->messageStatus->first();
        } else {
            // Fallback: relation load না থাকলে direct query (যেমন editMessage response-এ)
            $status = GroupMessageUserStatus::where('message_id', $this->id)
                ->where('user_id', $userId)
                ->first();
        }
        if ($this->type === 'broadcast') {
            $is_blurred   = 1;
            $is_viewed   = $status ? (int) $status->is_viewed  : false;
        } elseif ($this->message_type === 'reaction') {
            $is_blurred   = 0;
            $is_viewed   = 0;
        } else {
            // default case (যেমন normal API response)

            $is_blurred   = $status ? (int) $status->is_blurred : true;
            $is_viewed    = $status ? (int) $status->is_viewed  : false;
        }

        return [
            'id'           => $this->id,
            'group_id'     => (int) $this->group_id,
            'sender_id'    => (int) $this->sender_id,
            'text'         => $this->text,
            'file'         => $this->file ? asset($this->file) : null,
            'status'       => $this->status,

            // Per-user blur/view state — correctly isolated
            'is_blurred'   =>  $is_blurred,
            'is_viewed'    => $is_viewed,

            'message_type' => $this->message_type ?? 'normal',
            'created_at'   => $this->created_at?->diffForHumans(),
            'media_type'   => $this->resolveMediaType($this->file),

            // 'reply_to' => $this->whenLoaded('replyTo', function () use ($is_blurred) {
            //     $replied = $this->replyTo;
            //     if (!$replied) return null;

            //     return [
            //         'id'         => $replied->id,
            //         'sender_id'  => (int) $replied->sender_id,
            //         'text'       => $replied->text,
            //         'file'       => $replied->file ? asset($replied->file) : null,
            //         'media_type' => $this->resolveMediaType($replied->file),
            //         'is_blurred'   =>  $is_blurred ? 0 : 1, // reply message blur state should be same as current message for consistency
            //         'sender'     => [
            //             'id'         => $replied->sender->id ?? null,
            //             'first_name' => $replied->sender->first_name ?? null,
            //             'last_name'  => $replied->sender->last_name ?? null,
            //             'avatar'     => isset($replied->sender->avatar) && $replied->sender->avatar
            //                 ? asset($replied->sender->avatar)
            //                 : asset('default/default_image.jpg'),
            //         ],
            //     ];
            // }),

            'reply_to' => $this->whenLoaded('replyTo', function () use ($userId) {
                $replied = $this->replyTo;
                if (!$replied) return null;

                // Fetch the actual blur status of the replied message for this user
                // Use eager-loaded relation if available, else fall back to DB query
                if ($replied->relationLoaded('messageStatus')) {
                    $repliedStatus = $replied->messageStatus->first();
                } else {
                    $repliedStatus = GroupMessageUserStatus::where('message_id', $replied->id)
                        ->where('user_id', $userId)
                        ->first();
                }

                // Determine blur: use DB status if exists, otherwise infer from message properties
                $replyIsBlurred = $repliedStatus
                    ? (int) $repliedStatus->is_blurred
                    : (int) ($replied->file && $replied->message_type === 'normal');

                return [
                    'id'         => $replied->id,
                    'sender_id'  => (int) $replied->sender_id,
                    'text'       => $replied->text,
                    'file'       => $replied->file ? asset($replied->file) : null,
                    'media_type' => $this->resolveMediaType($replied->file),
                    'is_blurred' => $replyIsBlurred,
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
                'id'         => $this->sender->id ?? null,
                'first_name' => $this->sender->first_name ?? null,
                'last_name'  => $this->sender->last_name ?? null,
                'avatar'     => isset($this->sender->avatar) && $this->sender->avatar
                    ? asset($this->sender->avatar)
                    : asset('default/default_image.jpg'),
            ],

            'group' => [
                'id'     => $this->group->id ?? null,
                'name'   => $this->group->name ?? null,
                'avatar' => isset($this->group->avatar) && $this->group->avatar
                    ? asset($this->group->avatar)
                    : asset('default/default_image.jpg'),
            ],
        ];
    }

    protected function resolveMediaType(?string $file): ?string
    {
        if (!$file) return null;

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'heif', 'heic', 'tiff', 'raw']) => 'image',
            in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg', 'mpg'])               => 'video',
            in_array($extension, ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'])                                  => 'audio',
            in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'])              => 'document',
            in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])                                                 => 'archive',
            default                                                                                              => 'file',
        };
    }
}
