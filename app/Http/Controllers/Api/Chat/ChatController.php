<?php

namespace App\Http\Controllers\Api\Chat;

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
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\CombinedChatCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatController extends Controller
{
    use ApiResponse;

    /**
     * Send message
     */
    public function send(Request $request, $receiver_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable',
            'message_type' => 'nullable|in:normal,reaction', // New field
            'reply_to_id'  => 'nullable|exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $sender_id = Auth::guard('api')->id();
        $receiver_exist = User::where('id', $receiver_id)->first();

        if (!$receiver_exist || $receiver_id == $sender_id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or cannot chat with yourself',
                'data' => [],
                'code' => 200
            ]);
        }

        // Find or create room
        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (!$room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time() . '_' . $request->file('file'));
        }

        // Determine message type and blur status
        $messageType = $request->input('message_type', 'normal');
        $isBlurred = false;

        // If it's a normal message with media, it should be blurred
        if ($messageType === 'normal' && $file) {
            $isBlurred = true;
        }

        $text = $request->text ?? '';

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $chat = Chat::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'text' => $text,
            'file' => $file,
            'room_id' => $room->id,
            'status' => 'sent',
            'is_blurred' => $isBlurred,
            'is_viewed' => false,
            'message_type' => $messageType,
            'reply_to_id'  => $request->reply_to_id, // New
        ]);

        // $chat->load([
        //     'sender:id,first_name,last_name,avatar,last_activity_at',
        //     'receiver:id,first_name,last_name,avatar,last_activity_at',
        //     'room:id,user_one_id,user_two_id',
        //     'replyTo.sender:id,first_name,last_name,avatar',
        // ]);

        $chat->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'receiver:id,first_name,last_name,avatar,last_activity_at',
            'room:id,user_one_id,user_two_id',
            'replyTo.sender:id,first_name,last_name,avatar',
            'replyTo.parentReply:id,text,file'
        ]);

        broadcast(new MessageSendEvent($chat))->toOthers();

        // ========== NOTIFICATION PART ==========
        $receiver = User::find($receiver_id);
        if ($receiver && $receiver->firebaseTokens) {
            $senderName = Auth::guard('api')->user()->first_name . ' ' . Auth::guard('api')->user()->last_name;

            // Message preview create
            $messagePreview = '';
            if ($file) {
                // File type detect
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];

                if (in_array(strtolower($extension), $imageExtensions)) {
                    $messagePreview = '📷 Photo';
                } elseif (in_array(strtolower($extension), $videoExtensions)) {
                    $messagePreview = '🎥 Video';
                } else {
                    $messagePreview = '📎 File';
                }
            } else {
                $messagePreview = Str::limit($text, 50);
            }

            $notifyData = [
                'title' => $senderName,
                'body'  => $messagePreview,
                'icon'  => Auth::guard('api')->user()->avatar ?? config('settings.logo')
            ];

            foreach ($receiver->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Message Sent Successfully.',
            'data'    => ['chat' => new ChatResource($chat)],
            'code'    => 200
        ]);
    }

    /**
     * view message media
     */
    public function markAsViewed($message_id): JsonResponse
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
            ]);
        }

        // Mark as viewed (unblur)
        $chat->update([
            'is_viewed' => true,
            'is_blurred' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as viewed',
            'data' => ['chat' => $chat],
            'code' => 200
        ]);
    }

    /**
     * Get conversation with a specific user
     */
    public function conversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        // Mark messages as read
        Chat::where('receiver_id', $sender_id)
            ->where('sender_id', $receiver_id)
            ->update(['status' => 'read']);

        $perPage = 100000;
        $chat = Chat::query()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })
            ->with([
                'sender:id,first_name,last_name,avatar,last_activity_at',
                'receiver:id,first_name,last_name,avatar,last_activity_at',
                'room:id,user_one_id,user_two_id',
                'replyTo.sender:id,first_name,last_name,avatar',
                'replyTo.parentReply:id,text,file'
            ])
            ->orderBy('created_at')
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

        // Get or create room
        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (!$room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id
            ]);
        }

        // Check if sender is blocked by receiver
        $is_blocked = DB::table('user_blocks')
            ->where('user_id', $sender_id)
            ->where('block_user_id', $receiver_id)->orWhere(function ($query) use ($sender_id, $receiver_id) {
                $query->where('user_id', $receiver_id)
                    ->where('block_user_id', $sender_id);
            })
            ->exists();

        // Check if sender has blocked the receiver
        $block_by_me = DB::table('user_blocks')
            ->where('user_id', $sender_id) // I blocked?
            ->where('block_user_id', $receiver_id)
            ->exists();

        $data = [
            'receiver' => User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
                ->where('id', $receiver_id)
                ->first(),
            'sender' => User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
                ->where('id', $sender_id)
                ->first(),
            'room' => $room,
            'chat' => ChatMessageResource::collection($chat->items()),
            'pagination' => [
                'total' => $chat->total(),
                'current_page' => $chat->currentPage(),
                'last_page' => $chat->lastPage(),
                'per_page' => $chat->perPage(),
            ],
            'is_blocked' => $is_blocked,
            'block_by_me' => $block_by_me,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $data,
            'code' => 200
        ]);
    }


    /**
     * Mark all messages in a conversation as seen
     */
    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();

        if (!$receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with this user.', 'data' => [], 'code' => 200]);
        }

        $chat = Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $data = [
            'chat'  => $chat
        ];

        return response()->json([
            'success'  => true,
            'message'  => 'Message Seen Successfully.',
            'data'     => $data,
            'code'     => 200
        ]);
    }


    /**
     * Mark a single chat message as seen
     */
    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();


        $chat = Chat::where('id', $chat_id)->where('receiver_id', $sender_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message Seen Successfully',
            'data'    => $data,
            'code'    => 200
        ]);
    }

    /**
     * Get or create a chat room with a specific user
     */
    public function room($receiver_id)
    {
        $sender_id  = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();

        if (!$receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself.', 'data' => [], 'code' => 200]);
        }

        $room = Room::with(['userOne:id , first_name , last_name , email , avatar , last_activity_at', 'userTwo: id , first_name , last_name , avatar , last_activity_at'])
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
            })->first();


        if (!$room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $data = [
            'room' => $room
        ];

        return response()->json(['success' => true, 'message' => 'Group retrieved successfully.', 'data' => $data, 'code' => 200]);
    }


    /**
     * Search users by keyword
     */
    public function search(Request $request): JsonResponse
    {
        $user_id = Auth::id();

        $keyword = $request->get('keyword');
        $users = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where('first_name', 'LIKE', "%{$keyword}%")->orWhere('last_name', 'LIKE', "%{$keyword}%")->orWhere('email', 'LIKE', "%{$keyword}%")
            ->get();

        $data = [
            'users' => $users
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data'    => $data,
        ], 200);
    }


    /**
     * Delete chat with a specific user
     */
    public function deleteChat($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        // Find the room between these two users
        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
                'data'    => [],
                'code'    => 404
            ]);
        }

        // Soft delete all messages in this room
        Chat::where('room_id', $room->id)->delete();

        // Delete the room itself
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
            'data'    => [],
            'code'    => 200
        ]);
    }

    /*
    * Delete messages
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
                'data' => null,
                'code' => 422
            ], 422);
        }

        $authUser = Auth::guard('api')->user();

        // Delete only if user is sender or receiver
        $deleted = Chat::where('id', $request->message_id)
            ->where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->orWhere('receiver_id', $authUser->id);
            })
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully',
                'data' => ['deleted_count' => $deleted],
                'code' => 200
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Message not found or you do not have permission to delete it',
            'data' => null,
            'code' => 404
        ], 404);
    }


    /**
     * combined message list
     */
    public function listCombined(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $keyword = $request->get('keyword');
        $perPage = $request->get('per_page', 10);

        // --- Fetch one-to-one chat users ---
        $usersQuery = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $authUser->id)
            ->where(function ($query) use ($authUser) {
                $query->whereHas('senders', fn($q) => $q->where('receiver_id', $authUser->id))
                    ->orWhereHas('receivers', fn($q) => $q->where('sender_id', $authUser->id));
            });

        if ($keyword) {
            $usersQuery->where(function ($q) use ($keyword) {
                $q->where('first_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('last_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }

        $users = collect($usersQuery->get()->map(function ($user) use ($authUser) {
            $lastChat = Chat::where(function ($query) use ($user, $authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $user->id);
            })
                ->orWhere(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', $authUser->id);
                })
                ->latest()
                ->first();

            $room = Room::firstOrCreate([
                'user_one_id' => min($authUser->id, $user->id),
                'user_two_id' => max($authUser->id, $user->id),
            ]);

            return (object) [
                'type' => 'single',
                'room_id' => $room->id,
                'id' => $user->id,
                'name' => trim("{$user->first_name} {$user->last_name}"),
                'avatar' => $user->avatar ? asset($user->avatar) : asset('default/default_image.jpg'),
                // 'last_message' => $lastChat?->text,
                'last_message' => $lastChat?->text,
                'last_message_file' => $lastChat?->file,
                'last_message_time' => $lastChat?->created_at,
                'is_active' => $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5)),
                'member_count' => null,
            ];
        }));

        // --- Fetch groups ---
        $groupsQuery = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id));

        if ($keyword) {
            $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
        }

        //
        $groups = collect($groupsQuery->get()->map(function ($group) {
            $lastMessage = $group->messages()->latest()->first();

            return (object) [
                'type' => 'group',
                'room_id' => $group->id,
                'id' => $group->id,
                'name' => $group->name,
                'avatar' => $group->avatar ? asset($group->avatar) : asset('default/default_group.jpg'),
                // 'last_message' => $lastMessage?->text,
                'last_message' => $lastMessage?->text,
                'last_message_file' => $lastMessage?->file,
                'last_message_time' => $lastMessage?->created_at,
                'is_active' => false,
                'member_count' => $group->members()->count(),
            ];
        }));

        // --- Merge + sort ---
        $combined = $users->merge($groups)
            ->sortByDesc(fn($chat) => $chat->last_message_time)
            ->values();

        // --- Manual pagination ---
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
            'Combined chat list retrieved successfully.'
        );
    }
}
