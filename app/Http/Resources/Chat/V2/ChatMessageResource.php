<?php

namespace App\Http\Resources\Chat\V2;

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
            'file_type' => $this->file_type ?? $this->media_type,
            'thumbnail' => $this->thumbnail,
            'status' => $this->status,
            'is_blurred' => $this->is_blurred,
            'is_viewed' => $this->is_viewed,
            'message_type' => $this->message_type,
            'reply_to_id' => $this->reply_to_id,
            'forwarded_from' => $this->forwarded_from,

            // Computed fields
            'is_my_text' => $this->is_my_text ?? $this->isMine(),
            'should_show_blur' => $this->should_show_blur ?? false,
            'humanize_date' => $this->created_at->diffForHumans(),
            'short_text' => $this->short_text,
            'type' => $this->type,
            'media_type' => $this->media_type,
            'created_at' => $this->created_at->toISOString(),

            // Sender info
            'sender' => [
                'id' => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'last_name' => $this->sender->last_name,
                'avatar' => $this->sender->avatar,
                'last_activity_at' => $this->sender->last_activity_at,
            ],

            // Receiver info
            'receiver' => [
                'id' => $this->receiver->id,
                'first_name' => $this->receiver->first_name,
                'last_name' => $this->receiver->last_name,
                'avatar' => $this->receiver->avatar,
                'last_activity_at' => $this->receiver->last_activity_at,
            ],

            // Room info
            'room' => [
                'id' => $this->room->id,
                'user_one_id' => $this->room->user_one_id,
                'user_two_id' => $this->room->user_two_id,
            ],

            // Reply-to message (if exists)
            'reply_to' => $this->when($this->replyTo, function () {
                return [
                    'id' => $this->replyTo->id,
                    'sender_id' => $this->replyTo->sender_id,
                    'text' => $this->replyTo->text,
                    'file' => $this->replyTo->file,
                    'file_type' => $this->replyTo->file_type ?? $this->replyTo->media_type,
                    'sender' => [
                        'id' => $this->replyTo->sender->id,
                        'first_name' => $this->replyTo->sender->first_name,
                        'last_name' => $this->replyTo->sender->last_name,
                    ],
                ];
            }),

            // Forwarded from user (if exists)
            'forwarded_from_user' => $this->when($this->forwardedFromUser, function () {
                return [
                    'id' => $this->forwardedFromUser->id,
                    'first_name' => $this->forwardedFromUser->first_name,
                    'last_name' => $this->forwardedFromUser->last_name,
                ];
            }),
        ];
    }
}
