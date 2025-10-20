<?php

namespace App\Http\Controllers\Web\Backend;

use App\Events\MessageDeletedEvent;
use App\Events\MessageSendEvent;
use App\Events\TypingEvent;
use App\Events\MessageReadEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\MessageDeletion;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ChatManageController extends Controller
{
    public function index()
    {
        return view('backend.layouts.chat.index');
    }

    /**
     * Get chat list with unread counts and last message
     */
    public function list()
    {
        $authUser = Auth::user();

        $participants = ChatParticipant::where('user_id', $authUser->id)
            ->with(['room' => function ($query) use ($authUser) {
                $query->with(['firstUser', 'secondUser', 'lastMessage.sender']);
            }])
            ->orderByDesc('last_read_at')
            ->get();

        $chatList = $participants->map(function ($participant) use ($authUser) {
            $room = $participant->room;
            $otherUser = $room->first_user_id === $authUser->id
                ? $room->secondUser
                : $room->firstUser;

            return [
                'room_id' => $room->id,
                'user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->first_name . ' ' . $otherUser->last_name,
                    'avatar' => $otherUser->avatar ? asset($otherUser->avatar) : asset('default.jpg'),
                    'is_online' => $otherUser->isOnline(),
                    'last_activity' => $otherUser->last_activity_at?->diffForHumans(),
                ],
                'last_message' => $room->lastMessage ? [
                    'text' => $room->lastMessage->getShortText(),
                    'time' => $room->lastMessage->created_at->diffForHumans(),
                    'is_own' => $room->lastMessage->sender_id === $authUser->id,
                    'type' => $room->lastMessage->type,
                    'status' => $room->lastMessage->status,
                ] : null,
                'unread_count' => $participant->unread_count,
                'is_pinned' => $participant->is_pinned,
                'is_muted' => $participant->is_muted,
                'last_message_at' => $room->last_message_at?->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Chat list retrieved successfully',
            'data' => [
                'chats' => $chatList,
            ],
        ]);
    }

    /**
     * Search users to start new conversation
     */
    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $authUserId = Auth::id();

        $users = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $authUserId)
            ->where(function ($query) use ($keyword) {
                $query->where('first_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('last_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? asset($user->avatar) : asset('default.jpg'),
                    'is_online' => $user->isOnline(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => ['users' => $users],
        ]);
    }

    /**
     * Get or create room and fetch conversation
     */
    public function conversation($receiverId): JsonResponse
    {
        $senderId = Auth::id();

        $receiver = User::find($receiverId);
        if (!$receiver || $receiverId == $senderId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user',
            ], 400);
        }

        // Get or create room
        $room = $this->getOrCreateRoom($senderId, $receiverId);

        // Mark messages as read
        $this->markMessagesAsRead($room->id, $senderId);

        // Get messages with pagination
        $messages = Chat::where('room_id', $room->id)
            ->whereNotIn('id', function ($query) use ($senderId) {
                $query->select('chat_id')
                    ->from('message_deletions')
                    ->where('deleted_by', $senderId);
            })
            ->with(['sender', 'receiver', 'replyTo.sender'])
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($chat) use ($senderId) {
                return $this->formatMessage($chat, $senderId);
            });

        $participant = ChatParticipant::where('room_id', $room->id)
            ->where('user_id', $senderId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Conversation retrieved successfully',
            'data' => [
                'room_id' => $room->id,
                'receiver' => [
                    'id' => $receiver->id,
                    'name' => $receiver->first_name . ' ' . $receiver->last_name,
                    'avatar' => $receiver->avatar ? asset($receiver->avatar) : asset('default.jpg'),
                    'is_online' => $receiver->isOnline(),
                    'last_activity' => $receiver->last_activity_at?->diffForHumans(),
                ],
                'messages' => $messages,
                'unread_count' => $participant->unread_count ?? 0,
            ],
        ]);
    }

    /**
     * Send message with file support
     */
    public function send($receiverId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:51200', // 50MB
            'type' => 'required|in:text,image,video,audio,document,link',
            'reply_to_id' => 'nullable|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $senderId = Auth::id();

        if (!User::find($receiverId) || $receiverId == $senderId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receiver'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $room = $this->getOrCreateRoom($senderId, $receiverId);

            $chatData = [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'room_id' => $room->id,
                'message' => $request->message,
                'type' => $request->type,
                'reply_to_id' => $request->reply_to_id,
                'status' => 'sent',
            ];

            // Handle file upload based on type
            if ($request->hasFile('file')) {
                $fileData = $this->handleFileUpload($request->file('file'), $request->type);
                $chatData = array_merge($chatData, $fileData);
            }

            // Handle link preview
            if ($request->type === 'link' && $request->message) {
                $chatData['link_preview'] = $this->generateLinkPreview($request->message);
            }

            $chat = Chat::create($chatData);

            // Update room last message
            $room->update(['last_message_at' => now()]);

            // Update participants
            $this->updateParticipants($room->id, $senderId, $receiverId);

            // Load relationships
            $chat->load(['sender', 'receiver', 'replyTo.sender']);

            DB::commit();

            // Broadcast message
            broadcast(new MessageSendEvent($chat, $receiverId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'chat' => $this->formatMessage($chat, $senderId),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($roomId): JsonResponse
    {
        $userId = Auth::id();

        try {
            DB::beginTransaction();

            // Update message status
            Chat::where('room_id', $roomId)
                ->where('receiver_id', $userId)
                ->whereIn('status', ['sent', 'delivered'])
                ->update([
                    'status' => 'read',
                    'read_at' => now()
                ]);

            // Update participant
            $participant = ChatParticipant::where('room_id', $roomId)
                ->where('user_id', $userId)
                ->first();

            if ($participant) {
                $participant->update([
                    'unread_count' => 0,
                    'last_read_at' => now(),
                ]);
            }

            DB::commit();

            // Broadcast read event
            broadcast(new MessageReadEvent($roomId, $userId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read'
            ], 500);
        }
    }

    /**
     * Delete message
     */
    public function deleteMessage($chatId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delete_type' => 'required|in:for_me,for_everyone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $userId = Auth::id();
        $chat = Chat::find($chatId);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            if ($request->delete_type === 'for_everyone') {
                // Only sender can delete for everyone (within 1 hour)
                if ($chat->sender_id !== $userId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only delete your own messages'
                    ], 403);
                }

                if ($chat->created_at->diffInHours(now()) > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only delete messages within 1 hour'
                    ], 403);
                }

                $chat->update(['message' => 'This message was deleted']);

                // Delete file if exists
                if ($chat->file_path) {
                    Storage::delete($chat->file_path);
                }

                broadcast(new MessageDeletedEvent($chatId, $chat->room_id, 'for_everyone'))->toOthers();
            } else {
                // Delete for me
                MessageDeletion::create([
                    'chat_id' => $chatId,
                    'deleted_by' => $userId,
                    'deletion_type' => 'for_me',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message'
            ], 500);
        }
    }

    /**
     * Delete entire conversation
     */
    public function deleteConversation($receiverId): JsonResponse
    {
        $senderId = Auth::id();

        $room = Room::where(function ($query) use ($senderId, $receiverId) {
            $query->where('first_user_id', $senderId)
                ->where('second_user_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('first_user_id', $receiverId)
                ->where('second_user_id', $senderId);
        })->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Mark all messages as deleted for this user
            $messageIds = Chat::where('room_id', $room->id)->pluck('id');

            foreach ($messageIds as $messageId) {
                MessageDeletion::firstOrCreate([
                    'chat_id' => $messageId,
                    'deleted_by' => $senderId,
                ], [
                    'deletion_type' => 'for_me',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation'
            ], 500);
        }
    }

    /**
     * Typing indicator
     */
    public function typing($roomId, Request $request): JsonResponse
    {
        $userId = Auth::id();
        $isTyping = $request->boolean('is_typing');

        broadcast(new TypingEvent($roomId, $userId, $isTyping))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Typing status updated',
        ]);
    }

    // Helper Methods

    private function getOrCreateRoom($userId1, $userId2)
    {
        $room = Room::where(function ($query) use ($userId1, $userId2) {
            $query->where('first_user_id', $userId1)
                ->where('second_user_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('first_user_id', $userId2)
                ->where('second_user_id', $userId1);
        })->first();

        if (!$room) {
            $room = Room::create([
                'first_user_id' => min($userId1, $userId2),
                'second_user_id' => max($userId1, $userId2),
                'type' => 'private',
            ]);

            // Create participants
            ChatParticipant::create([
                'room_id' => $room->id,
                'user_id' => $userId1,
                'joined_at' => now(),
            ]);

            ChatParticipant::create([
                'room_id' => $room->id,
                'user_id' => $userId2,
                'joined_at' => now(),
            ]);
        }

        return $room;
    }

    private function markMessagesAsRead($roomId, $userId)
    {
        Chat::where('room_id', $roomId)
            ->where('receiver_id', $userId)
            ->whereIn('status', ['sent', 'delivered'])
            ->update([
                'status' => 'read',
                'read_at' => now()
            ]);

        $participant = ChatParticipant::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->first();

        if ($participant) {
            $participant->update([
                'unread_count' => 0,
                'last_read_at' => now(),
            ]);
        }
    }

    private function updateParticipants($roomId, $senderId, $receiverId)
    {
        // Update receiver's unread count
        $receiverParticipant = ChatParticipant::where('room_id', $roomId)
            ->where('user_id', $receiverId)
            ->first();

        if ($receiverParticipant) {
            $receiverParticipant->increment('unread_count');
        }

        // Update sender's last read
        $senderParticipant = ChatParticipant::where('room_id', $roomId)
            ->where('user_id', $senderId)
            ->first();

        if ($senderParticipant) {
            $senderParticipant->update(['last_read_at' => now()]);
        }
    }

    private function handleFileUpload($file, $type)
    {
        $data = [];
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        switch ($type) {
            case 'image':
                $path = $file->storeAs('chat/images', $filename, 'public');

                // Create thumbnail
                $thumbnailPath = 'chat/thumbnails/' . $filename;
                $image = Image::make($file);
                $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                Storage::disk('public')->put($thumbnailPath, $image->encode());

                $data['thumbnail_path'] = $thumbnailPath;
                break;

            case 'video':
                $path = $file->storeAs('chat/videos', $filename, 'public');
                // You can add video thumbnail generation here
                break;

            case 'audio':
                $path = $file->storeAs('chat/audios', $filename, 'public');
                break;

            case 'document':
                $path = $file->storeAs('chat/documents', $filename, 'public');
                break;

            default:
                $path = $file->storeAs('chat/files', $filename, 'public');
        }

        $data['file_path'] = $path;
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_type'] = $file->getMimeType();
        $data['file_size'] = $file->getSize();

        return $data;
    }

    private function generateLinkPreview($url)
    {
        // Simple link preview - you can use a service like linkpreview.net API
        return json_encode([
            'url' => $url,
            'title' => 'Link Preview',
            'description' => 'Click to open',
            'image' => null,
        ]);
    }

    private function formatMessage($chat, $userId)
    {
        return [
            'id' => $chat->id,
            'message' => $chat->message,
            'type' => $chat->type,
            'file' => $chat->file_path ? asset('storage/' . $chat->file_path) : null,
            'file_name' => $chat->file_name,
            'file_size' => $chat->file_size,
            'thumbnail' => $chat->thumbnail_path ? asset('storage/' . $chat->thumbnail_path) : null,
            'is_own' => $chat->sender_id === $userId,
            'sender' => [
                'id' => $chat->sender->id,
                'name' => $chat->sender->first_name . ' ' . $chat->sender->last_name,
                'avatar' => $chat->sender->avatar ? asset($chat->sender->avatar) : asset('default.jpg'),
            ],
            'reply_to' => $chat->replyTo ? [
                'id' => $chat->replyTo->id,
                'message' => $chat->replyTo->message,
                'sender_name' => $chat->replyTo->sender->first_name,
            ] : null,
            'status' => $chat->status,
            'created_at' => $chat->created_at->format('H:i'),
            'time' => $chat->created_at->diffForHumans(),
        ];
    }
}
