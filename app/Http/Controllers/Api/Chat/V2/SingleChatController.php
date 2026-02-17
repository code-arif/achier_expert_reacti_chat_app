<?php

namespace App\Http\Controllers\Api\Chat\V2;

use Exception;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Models\Group;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\Chat\V2\ChatResource;
use App\Http\Resources\CombinedChatCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class SingleChatController extends Controller
{
    use ApiResponse;

    /**
     * Send message with optimized S3 upload and real-time delivery
     * Features: Typing indicators, delivery receipts, media optimization
     */
    public function send(Request $request, $receiver_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:102400', // 50MB max
            'message_type' => 'nullable|in:normal,reaction,reply',
            'reply_to_id' => 'nullable|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 422
            ], 422);
        }

        $sender_id = Auth::guard('api')->id();
        $receiver = User::find($receiver_id);

        if (!$receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or cannot chat with yourself',
                'code' => 400
            ], 400);
        }

        // Check if blocked
        $isBlocked = DB::table('user_blocks')
            ->where(function ($query) use ($sender_id, $receiver_id) {
                $query->where('user_id', $sender_id)->where('block_user_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($sender_id, $receiver_id) {
                $query->where('user_id', $receiver_id)->where('block_user_id', $sender_id);
            })
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send message to this user',
                'code' => 403
            ], 403);
        }

        // Find or create room
        $room = Room::firstOrCreate([
            'user_one_id' => min($sender_id, $receiver_id),
            'user_two_id' => max($sender_id, $receiver_id)
        ]);

        $file = null;
        $fileType = null;
        $thumbnailPath = null;
        $filePath = null;

        // Upload file to S3 with proper error handling
        if ($request->hasFile('file')) {
            try {
                $uploadedFile = $request->file('file');
                $mimeType = $uploadedFile->getMimeType();

                Log::info('Starting file upload', [
                    'mime_type' => $mimeType,
                    'size' => $uploadedFile->getSize(),
                    'original_name' => $uploadedFile->getClientOriginalName()
                ]);

                // Determine file type
                if (Str::startsWith($mimeType, 'image/')) {
                    $fileType = 'image';
                } elseif (Str::startsWith($mimeType, 'video/')) {
                    $fileType = 'video';
                } elseif (Str::startsWith($mimeType, 'audio/')) {
                    $fileType = 'audio';
                } else {
                    $fileType = 'document';
                }

                // Generate unique filename
                $extension = $uploadedFile->getClientOriginalExtension();
                $fileName = time() . '_' . Str::random(10) . '.' . $extension;
                $filePath = "chat/{$room->id}/{$fileName}";

                Log::info('Uploading to S3', [
                    'path' => $filePath,
                    'bucket' => config('filesystems.disks.s3.bucket'),
                    'region' => config('filesystems.disks.s3.region')
                ]);

                // Upload to S3 using putFileAs (better method)
                $s3Path = Storage::disk('s3')->putFileAs(
                    "chat/{$room->id}",
                    $uploadedFile,
                    $fileName,
                    'public' // ACL
                );

                if ($s3Path) {
                    // Get the full S3 URL
                    $file = Storage::disk('s3')->url($s3Path);

                    Log::info('File uploaded successfully to S3', [
                        'path' => $s3Path,
                        'url' => $file
                    ]);
                } else {
                    throw new Exception('S3 upload failed - no path returned');
                }
            } catch (Exception $e) {
                Log::error('S3 Upload Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file_path' => $filePath ?? 'unknown'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file: ' . $e->getMessage(),
                    'code' => 500
                ], 500);
            }
        }

        $text = $request->text ?? '';

        // Ensure UTF-8 encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $messageType = $request->input('message_type', 'normal');
        $isBlurred = false;

        // Blur media for normal messages
        if ($messageType === 'normal' && $file) {
            $isBlurred = true;
        }

        // Create message with transaction
        DB::beginTransaction();
        try {
            $chat = Chat::create([
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'text' => $text,
                'file' => $file,
                'file_type' => $fileType,
                'thumbnail' => $thumbnailPath,
                'room_id' => $room->id,
                'status' => 'sent',
                'is_blurred' => $isBlurred,
                'is_viewed' => false,
                'message_type' => $messageType,
                'reply_to_id' => $request->reply_to_id,
            ]);

            // Update room's last message timestamp
            $room->touch();

            DB::commit();

            Log::info('Message created successfully', [
                'message_id' => $chat->id,
                'has_file' => !is_null($file)
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to create message', [
                'error' => $e->getMessage()
            ]);

            // Delete uploaded file if message creation fails
            if ($filePath) {
                try {
                    Storage::disk('s3')->delete($filePath);
                    Log::info('Rolled back S3 file upload');
                } catch (Exception $deleteError) {
                    Log::error('Failed to delete S3 file during rollback', [
                        'error' => $deleteError->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }

        // Eager load relationships
        $chat->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'receiver:id,first_name,last_name,avatar,last_activity_at',
            'room:id,user_one_id,user_two_id',
            'replyTo:id,sender_id,text,file'
        ]);

        // Broadcast message via WebSocket
        try {
            broadcast(new MessageSendEvent($chat))->toOthers();
        } catch (Exception $e) {
            Log::warning('Failed to broadcast message', [
                'error' => $e->getMessage()
            ]);
        }

        // Send push notification
        try {
            $this->sendPushNotification($receiver, $sender_id, $text, $file, $fileType);
        } catch (Exception $e) {
            Log::warning('Failed to send push notification', [
                'error' => $e->getMessage()
            ]);
        }

        // Clear cache
        Cache::forget("chat_list_user_{$sender_id}");
        Cache::forget("chat_list_user_{$receiver_id}");

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => ['chat' => new ChatResource($chat)],
            'code' => 200
        ]);
    }

    /**
     * Generate thumbnail for images
     */
    private function generateThumbnail($file): ?string
    {
        try {
            // Use Intervention Image or similar library for thumbnail generation
            // For now, we'll just upload the original
            // TODO: Implement actual thumbnail generation
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Generate thumbnail for videos
     */
    private function generateVideoThumbnail($file): ?string
    {
        try {
            // Use FFmpeg to generate video thumbnail
            // TODO: Implement actual video thumbnail generation
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Send push notification with optimized message preview
     */
    private function sendPushNotification($receiver, $senderId, $text, $file, $fileType)
    {
        if (!$receiver || !$receiver->firebaseTokens->count()) {
            return;
        }

        $sender = User::select('id', 'first_name', 'last_name', 'avatar')
            ->find($senderId);

        $senderName = trim("{$sender->first_name} {$sender->last_name}");

        // Create message preview
        $messagePreview = '';
        if ($file) {
            switch ($fileType) {
                case 'image':
                    $messagePreview = '📷 Photo';
                    break;
                case 'video':
                    $messagePreview = '🎥 Video';
                    break;
                case 'audio':
                    $messagePreview = '🎵 Audio';
                    break;
                case 'document':
                    $messagePreview = '📎 Document';
                    break;
                default:
                    $messagePreview = '📁 File';
            }
        } else {
            $messagePreview = Str::limit($text, 50);
        }

        $notifyData = [
            'title' => $senderName,
            'body' => $messagePreview,
            'icon' => $sender->avatar ?? config('settings.logo'),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'sender_id' => $senderId,
        ];

        // Send to all registered devices
        foreach ($receiver->firebaseTokens as $firebaseToken) {
            Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
        }
    }

    /**
     * Mark message media as viewed (unblur)
     */
    public function markAsViewed(Request $request, $message_id): JsonResponse
    {
        $user_id = Auth::guard('api')->id();

        $chat = Chat::where('id', $message_id)
            ->where('receiver_id', $user_id)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'code' => 404
            ], 404);
        }

        // Mark as viewed (unblur)
        $chat->update([
            'is_viewed' => true,
            'is_blurred' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as viewed',
            'data' => ['chat' => new ChatResource($chat)],
            'code' => 200
        ]);
    }

    /**
     * Get conversation with optimized pagination and eager loading
     */
    public function conversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        // Validate receiver exists
        $receiver = User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
            ->find($receiver_id);

        if (!$receiver || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'code' => 404
            ], 404);
        }

        // Mark messages as read in bulk
        Chat::where('receiver_id', $sender_id)
            ->where('sender_id', $receiver_id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        // Get or create room
        $room = Room::firstOrCreate([
            'user_one_id' => min($sender_id, $receiver_id),
            'user_two_id' => max($sender_id, $receiver_id)
        ]);

        // Optimized query with proper indexing
        $perPage = request()->get('per_page', 50);
        $page = request()->get('page', 1);

        $chat = Chat::where('room_id', $room->id)
            ->with([
                'sender:id,first_name,last_name,avatar,last_activity_at',
                'receiver:id,first_name,last_name,avatar,last_activity_at',
                'room:id,user_one_id,user_two_id',
                'replyTo:id,sender_id,text,file' // Load reply-to messages
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform messages
        $chat->getCollection()->transform(function ($message) use ($sender_id) {
            $message->is_my_text = $message->sender_id === $sender_id;
            $message->should_show_blur = false;

            if ($message->receiver_id === $sender_id && $message->is_blurred && !$message->is_viewed) {
                $message->should_show_blur = true;
            }

            return $message;
        });

        // Check block status
        $blockStatus = $this->checkBlockStatus($sender_id, $receiver_id);

        $sender = User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
            ->find($sender_id);

        $data = [
            'receiver' => $receiver,
            'sender' => $sender,
            'room' => $room,
            'chat' => ChatMessageResource::collection($chat->items()),
            'pagination' => [
                'total' => $chat->total(),
                'current_page' => $chat->currentPage(),
                'last_page' => $chat->lastPage(),
                'per_page' => $chat->perPage(),
                'from' => $chat->firstItem(),
                'to' => $chat->lastItem(),
            ],
            'is_blocked' => $blockStatus['is_blocked'],
            'block_by_me' => $blockStatus['block_by_me'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $data,
            'code' => 200
        ]);
    }

    /**
     * Check block status between users
     */
    private function checkBlockStatus($user_id, $other_user_id): array
    {
        $blockQuery = DB::table('user_blocks')
            ->where(function ($query) use ($user_id, $other_user_id) {
                $query->where('user_id', $user_id)->where('block_user_id', $other_user_id);
            })
            ->orWhere(function ($query) use ($user_id, $other_user_id) {
                $query->where('user_id', $other_user_id)->where('block_user_id', $user_id);
            })
            ->first();

        return [
            'is_blocked' => (bool) $blockQuery,
            'block_by_me' => $blockQuery && $blockQuery->user_id == $user_id,
        ];
    }

    /**
     * Mark all messages as seen
     */
    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        if (!User::find($receiver_id) || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user',
                'code' => 400
            ], 400);
        }

        $updated = Chat::where('receiver_id', $sender_id)
            ->where('sender_id', $receiver_id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'message' => 'All messages marked as read',
            'data' => ['updated_count' => $updated],
            'code' => 200
        ]);
    }

    /**
     * Mark single message as seen
     */
    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $updated = Chat::where('id', $chat_id)
            ->where('receiver_id', $sender_id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found or already read',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read',
            'code' => 200
        ]);
    }

    /**
     * Get or create room
     */
    public function room($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        if (!User::find($receiver_id) || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user',
                'code' => 400
            ], 400);
        }

        $room = Room::with([
            'userOne:id,first_name,last_name,email,avatar,last_activity_at',
            'userTwo:id,first_name,last_name,email,avatar,last_activity_at'
        ])
            ->firstOrCreate([
                'user_one_id' => min($sender_id, $receiver_id),
                'user_two_id' => max($sender_id, $receiver_id)
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Room retrieved successfully',
            'data' => ['room' => $room],
            'code' => 200
        ]);
    }

    /**
     * Search users with optimized query
     */
    public function search(Request $request): JsonResponse
    {
        $user_id = Auth::guard('api')->id();
        $keyword = $request->get('keyword');

        if (!$keyword) {
            return response()->json([
                'success' => false,
                'message' => 'Search keyword required',
                'code' => 400
            ], 400);
        }

        $users = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where(function ($query) use ($keyword) {
                $query->where('first_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('last_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('username', 'LIKE', "%{$keyword}%");
            })
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Search completed successfully',
            'data' => ['users' => $users],
            'code' => 200
        ]);
    }

    /**
     * Delete entire conversation
     */
    public function deleteChat($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $room = Room::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
                'code' => 404
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Get all files to delete from S3
            $messages = Chat::where('room_id', $room->id)
                ->whereNotNull('file')
                ->get(['file']);

            // Delete files from S3
            foreach ($messages as $message) {
                if ($message->file && Str::startsWith($message->file, 'https://')) {
                    $filePath = parse_url($message->file, PHP_URL_PATH);
                    Storage::disk('s3')->delete($filePath);
                }
            }

            // Soft delete messages
            Chat::where('room_id', $room->id)->delete();

            // Delete room
            $room->delete();

            DB::commit();

            // Clear cache
            Cache::forget("chat_list_user_{$sender_id}");
            Cache::forget("chat_list_user_{$receiver_id}");

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully',
                'code' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Delete single message
     */
    public function deleteMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 422
            ], 422);
        }

        $authUser = Auth::guard('api')->user();

        $message = Chat::where('id', $request->message_id)
            ->where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->orWhere('receiver_id', $authUser->id);
            })
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'code' => 404
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete file from S3 if exists
            if ($message->file && Str::startsWith($message->file, 'https://')) {
                $filePath = parse_url($message->file, PHP_URL_PATH);
                Storage::disk('s3')->delete($filePath);
            }

            $message->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully',
                'code' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Combined chat list with optimization and caching
     */
    public function listCombined(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $keyword = $request->get('keyword');
        $perPage = $request->get('per_page', 20);

        // Cache key
        $cacheKey = "chat_list_user_{$authUser->id}_keyword_" . md5($keyword ?? '');

        // Try to get from cache (cache for 30 seconds for real-time feel)
        if (!$keyword) {
            $combined = Cache::remember($cacheKey, 30, function () use ($authUser, $keyword, $perPage) {
                return $this->getCombinedChatList($authUser, $keyword, $perPage);
            });
        } else {
            $combined = $this->getCombinedChatList($authUser, $keyword, $perPage);
        }

        // Manual pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedData = $combined->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pagedData,
            $combined->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->success(
            new CombinedChatCollection($paginator),
            'Chat list retrieved successfully'
        );
    }

    /**
     * Get combined chat list (users + groups)
     */
    private function getCombinedChatList($authUser, $keyword, $perPage)
    {
        // Optimized query for one-to-one chats
        $usersQuery = User::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.avatar', 'users.last_activity_at')
            ->where('users.id', '!=', $authUser->id)
            ->whereExists(function ($query) use ($authUser) {
                $query->select(DB::raw(1))
                    ->from('chats')
                    ->whereColumn('chats.sender_id', 'users.id')
                    ->where('chats.receiver_id', $authUser->id)
                    ->orWhere(function ($q) use ($authUser) {
                        $q->whereColumn('chats.receiver_id', 'users.id')
                            ->where('chats.sender_id', $authUser->id);
                    });
            });

        if ($keyword) {
            $usersQuery->where(function ($q) use ($keyword) {
                $q->where('first_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('last_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('username', 'LIKE', "%{$keyword}%");
            });
        }

        $users = collect($usersQuery->get()->map(function ($user) use ($authUser) {
            // Get last message efficiently
            $lastChat = Chat::where('room_id', function ($query) use ($user, $authUser) {
                $query->select('id')
                    ->from('rooms')
                    ->where(function ($q) use ($user, $authUser) {
                        $q->where('user_one_id', min($authUser->id, $user->id))
                            ->where('user_two_id', max($authUser->id, $user->id));
                    })
                    ->limit(1);
            })
                ->latest()
                ->first(['text', 'file', 'file_type', 'created_at']);

            // Get or create room
            $room = Room::firstOrCreate([
                'user_one_id' => min($authUser->id, $user->id),
                'user_two_id' => max($authUser->id, $user->id),
            ]);

            // Get unread count
            $unreadCount = Chat::where('receiver_id', $authUser->id)
                ->where('sender_id', $user->id)
                ->where('status', '!=', 'read')
                ->count();

            return (object) [
                'type' => 'single',
                'room_id' => $room->id,
                'id' => $user->id,
                'name' => trim("{$user->first_name} {$user->last_name}"),
                'avatar' => $user->avatar ? asset($user->avatar) : asset('default/default_image.jpg'),
                'last_message' => $lastChat?->text,
                'last_message_file' => $lastChat?->file,
                'last_message_time' => $lastChat?->created_at,
                'is_active' => $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5)),
                'member_count' => null,
                'unread_count' => $unreadCount,
            ];
        }));

        // Optimized query for groups
        $groupsQuery = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id));

        if ($keyword) {
            $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
        }

        $groups = collect($groupsQuery->get()->map(function ($group) use ($authUser) {
            $lastMessage = $group->messages()->latest()->first(['text', 'file', 'file_type', 'created_at']);

            // Get unread count for group
            $unreadCount = $group->messages()
                ->where('sender_id', '!=', $authUser->id)
                ->whereDoesntHave('readBy', function ($query) use ($authUser) {
                    $query->where('user_id', $authUser->id);
                })
                ->count();

            return (object) [
                'type' => 'group',
                'room_id' => $group->id,
                'id' => $group->id,
                'name' => $group->name,
                'avatar' => $group->avatar ? asset($group->avatar) : asset('default/default_group.jpg'),
                'last_message' => $lastMessage?->text,
                'last_message_file' => $lastMessage?->file,
                'last_message_time' => $lastMessage?->created_at,
                'is_active' => false,
                'member_count' => $group->members()->count(),
                'unread_count' => $unreadCount,
            ];
        }));

        // Merge and sort by last message time
        return $users->merge($groups)
            ->sortByDesc(fn($chat) => $chat->last_message_time)
            ->values();
    }

    /**
     * Forward message to another user/group
     */
    public function forwardMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:chats,id',
            'receiver_ids' => 'required|array',
            'receiver_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 422
            ], 422);
        }

        $authUser = Auth::guard('api')->user();
        $originalMessage = Chat::with('sender', 'receiver')->find($request->message_id);

        if (!$originalMessage) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'code' => 404
            ], 404);
        }

        $forwardedMessages = [];

        DB::beginTransaction();
        try {
            foreach ($request->receiver_ids as $receiverId) {
                if ($receiverId == $authUser->id) {
                    continue; // Skip self
                }

                // Create room
                $room = Room::firstOrCreate([
                    'user_one_id' => min($authUser->id, $receiverId),
                    'user_two_id' => max($authUser->id, $receiverId)
                ]);

                // Forward message
                $forwardedMessage = Chat::create([
                    'sender_id' => $authUser->id,
                    'receiver_id' => $receiverId,
                    'text' => $originalMessage->text,
                    'file' => $originalMessage->file,
                    'file_type' => $originalMessage->file_type,
                    'room_id' => $room->id,
                    'status' => 'sent',
                    'is_blurred' => false,
                    'is_viewed' => false,
                    'message_type' => 'normal',
                    'forwarded_from' => $originalMessage->sender_id,
                ]);

                $forwardedMessages[] = $forwardedMessage;

                // Broadcast
                broadcast(new MessageSendEvent($forwardedMessage))->toOthers();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message forwarded successfully',
                'data' => ['forwarded_count' => count($forwardedMessages)],
                'code' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to forward message',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Update typing status (used with WebSocket)
     */
    public function typingStatus(Request $request, $receiver_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 422
            ], 422);
        }

        $authUser = Auth::guard('api')->user();

        // Broadcast typing status
        broadcast(new \App\Events\UserTypingEvent([
            'user_id' => $authUser->id,
            'receiver_id' => $receiver_id,
            'is_typing' => $request->is_typing,
        ]))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Typing status updated',
            'code' => 200
        ]);
    }
}
