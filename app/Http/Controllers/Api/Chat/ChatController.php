<?php

namespace App\Http\Controllers\Api\Chat;

use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Models\Group;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CombinedChatCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatController extends Controller
{
    use ApiResponse;

    /**
     * List all users with their last chat messages
     */
    // public function list(Request $request): JsonResponse
    // {
    //     $authUser = Auth::guard('api')->user();
    //     $keyword = $request->get('keyword');

    //     // Users query
    //     $usersQuery = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
    //         ->where('id', '!=', $authUser->id)
    //         ->where(function ($query) use ($authUser) {
    //             $query->whereHas('senders', function ($q) use ($authUser) {
    //                 $q->where('receiver_id', $authUser->id);
    //             })
    //                 ->orWhereHas('receivers', function ($q) use ($authUser) {
    //                     $q->where('sender_id', $authUser->id);
    //                 });
    //         });

    //     // Apply search keyword if exists
    //     if ($keyword) {
    //         $usersQuery->where(function ($q) use ($keyword) {
    //             $q->where('first_name', 'LIKE', "%{$keyword}%")
    //                 ->orWhere('last_name', 'LIKE', "%{$keyword}%")
    //                 ->orWhere('email', 'LIKE', "%{$keyword}%");
    //         });
    //     }

    //     $users = $usersQuery->get();

    //     // Map last chat + active flag
    //     $userWithMessages = $users->map(function ($user) use ($authUser) {
    //         $lastChat = Chat::where(function ($query) use ($user, $authUser) {
    //             $query->where('sender_id', $authUser->id)
    //                 ->where('receiver_id', $user->id);
    //         })
    //             ->orWhere(function ($query) use ($user, $authUser) {
    //                 $query->where('sender_id', $user->id)
    //                     ->where('receiver_id', $authUser->id);
    //             })
    //             ->latest()
    //             ->first();

    //         $user->last_chat = $lastChat;

    //         // Active check (within 5 minutes)
    //         $user->is_active = $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5));

    //         return $user;
    //     });

    //     // Sort by last chat
    //     $sortedUsers = $userWithMessages->sortByDesc(function ($user) {
    //         return optional($user->last_chat)->created_at;
    //     })->values();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Chat retrieved Successfully',
    //         'data' => ['users' => $sortedUsers],
    //     ], 200);
    // }


    /*
    * Send a message to a user
     */
    public function send(Request $request, $receiver_id): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file'  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp3,wav,mp4,mov,avi,txt,pdf,doc,docx,xls,xlsx,zip,rar|max:51200'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();

        if (!$receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with your self', 'data' => [],  'code' => 200]);
        }

        //Find Existing Room (or Create New)
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
            $file = Helper::fileUpload($request->file('file'),  'chat', time() . '_' . $request->file('file'));


        }

        $chat = Chat::create([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'text'        => $request->text,
            'file'        => $file,
            'room_id'     => $room->id,
            'status'      => 'sent'
        ]);

        $chat->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'receiver:id,first_name,last_name,avatar,last_activity_at',
            'room:id,user_one_id,user_two_id'
        ]);

        // broadcast(new MessageSendEvent($chat));
        broadcast(new MessageSendEvent($chat))->toOthers();

        $data = [
            'chat' => $chat
        ];

        return response()->json([
            'success'  => true,
            'message'  => 'Message Sent Successfully.',
            'data'     => $data,
            'code'     => 200
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

        // Paginate chat messages
        $perPage = 50; // fixed or you can make it dynamic via request
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
            ])
            ->orderBy('created_at')
            ->paginate($perPage);

        // check is my text
        $chat->getCollection()->transform(function ($message) use ($sender_id) {
            $message->is_my_text = $message->sender_id === $sender_id;
            return $message;
        });

        // Get or create chat room
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

        // Response data
        $data = [
            'receiver' => User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
                ->where('id', $receiver_id)
                ->first(),
            'sender' => User::select('id', 'first_name', 'last_name', 'avatar', 'last_activity_at')
                ->where('id', $sender_id)
                ->first(),
            'room' => $room,
            'chat' => $chat->items(), // only chat collection
            'pagination' => [
                'total'        => $chat->total(),
                'current_page' => $chat->currentPage(),
                'last_page'    => $chat->lastPage(),
                'per_page'     => $chat->perPage(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $data,
            'code'    => 200
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
    public function deleteMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('api')->user();

        // Delete only messages where user is sender or receiver
        $deleted = Chat::whereIn('id', $request->message_ids)
            ->where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->orWhere('receiver_id', $authUser->id);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Messages deleted successfully',
            'data' => ['deleted_count' => $deleted],
            'code' => 200
        ]);
    }

    /**
     * combined message list
     */
    // public function listCombined(Request $request): JsonResponse
    // {
    //     $authUser = Auth::guard('api')->user();
    //     $keyword = $request->get('keyword');

    //     // --- 1️⃣ Fetch one-to-one chat users ---
    //     $usersQuery = User::select('id', 'first_name', 'last_name', 'email', 'avatar', 'last_activity_at')
    //         ->where('id', '!=', $authUser->id)
    //         ->where(function ($query) use ($authUser) {
    //             $query->whereHas('senders', fn($q) => $q->where('receiver_id', $authUser->id))
    //                 ->orWhereHas('receivers', fn($q) => $q->where('sender_id', $authUser->id));
    //         });

    //     if ($keyword) {
    //         $usersQuery->where(function ($q) use ($keyword) {
    //             $q->where('first_name', 'LIKE', "%{$keyword}%")
    //                 ->orWhere('last_name', 'LIKE', "%{$keyword}%")
    //                 ->orWhere('email', 'LIKE', "%{$keyword}%");
    //         });
    //     }

    //     $users = $usersQuery->get()->map(function ($user) use ($authUser) {
    //         $lastChat = Chat::where(function ($query) use ($user, $authUser) {
    //             $query->where('sender_id', $authUser->id)->where('receiver_id', $user->id);
    //         })
    //             ->orWhere(function ($query) use ($user, $authUser) {
    //                 $query->where('sender_id', $user->id)->where('receiver_id', $authUser->id);
    //             })
    //             ->latest()
    //             ->first();

    //         return (object) [
    //             'type' => 'single',
    //             'id' => $user->id,
    //             'name' => trim("{$user->first_name} {$user->last_name}"),
    //             'avatar' => $user->avatar ? asset($user->avatar) : asset('default/default_image.jpg'),
    //             'last_message' => $lastChat?->text,
    //             'last_message_time' => $lastChat?->created_at,
    //             'is_active' => $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5)),
    //         ];
    //     });

    //     // --- 2️⃣ Fetch groups ---
    //     $groupsQuery = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id));

    //     if ($keyword) {
    //         $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
    //     }

    //     $groups = $groupsQuery->get()->map(function ($group) use ($authUser) {
    //         $lastMessage = $group->messages()->latest()->first();
    //         return (object) [
    //             'type' => 'group',
    //             'id' => $group->id,
    //             'name' => $group->name,
    //             'avatar' => $group->avatar ? asset($group->avatar) : asset('default/default_group.jpg'),
    //             'last_message' => $lastMessage?->text,
    //             'last_message_time' => $lastMessage?->created_at,
    //             'member_count' => $group->members()->count(),
    //         ];
    //     });

    //     // --- 3️⃣ Merge + sort ---
    //     $combined = $users->merge($groups)
    //         ->sortByDesc(fn($chat) => $chat->last_message_time)
    //         ->values();

    //     // --- 4️⃣ Return unified response ---
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Combined chat list retrieved successfully.',
    //         'data' => $combined,
    //     ]);
    // }


    public function listCombined(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $keyword = $request->get('keyword');
        $perPage = $request->get('per_page', 10);

        // --- 1️⃣ Fetch one-to-one chat users ---
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

        $users = $usersQuery->get()->map(function ($user) use ($authUser) {
            $lastChat = Chat::where(function ($query) use ($user, $authUser) {
                $query->where('sender_id', $authUser->id)->where('receiver_id', $user->id);
            })
                ->orWhere(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $user->id)->where('receiver_id', $authUser->id);
                })
                ->latest()
                ->first();

            // determine or create room_id
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
                'last_message' => $lastChat?->text,
                'last_message_time' => $lastChat?->created_at,
                'is_active' => $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5)),
                'member_count' => null,
            ];
        });

        // --- Fetch groups ---
        $groupsQuery = Group::whereHas('members', fn($q) => $q->where('user_id', $authUser->id));

        if ($keyword) {
            $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
        }

        $groups = $groupsQuery->get()->map(function ($group) {
            $lastMessage = $group->messages()->latest()->first();

            return (object) [
                'type' => 'group',
                'room_id' => $group->id, // or $group->chat_room_id if exists
                'id' => $group->id,
                'name' => $group->name,
                'avatar' => $group->avatar ? asset($group->avatar) : asset('default/default_group.jpg'),
                'last_message' => $lastMessage?->text,
                'last_message_time' => $lastMessage?->created_at,
                'is_active' => false,
                'member_count' => $group->members()->count(),
            ];
        });


        // --- Merge + sort ---
        $combined = $users->merge($groups)
            ->sortByDesc(fn($chat) => $chat->last_message_time)
            ->values();

        // --- Manual pagination (since we merged collections) ---
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
