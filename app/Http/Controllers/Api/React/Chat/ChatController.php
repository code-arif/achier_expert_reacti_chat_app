<?php

namespace App\Http\Controllers\Api\React\Chat;

use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{

    /**
     * List all users with their last chat messages
     */
    public function list(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $keyword = $request->get('keyword');

        // Users query
        $usersQuery = User::select('id', 'f_name', 'l_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $authUser->id)
            ->where(function ($query) use ($authUser) {
                $query->whereHas('senders', function ($q) use ($authUser) {
                    $q->where('receiver_id', $authUser->id);
                })
                    ->orWhereHas('receivers', function ($q) use ($authUser) {
                        $q->where('sender_id', $authUser->id);
                    });
            });

        // Apply search keyword if exists
        if ($keyword) {
            $usersQuery->where(function ($q) use ($keyword) {
                $q->where('f_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('l_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }

        $users = $usersQuery->get();

        // Map last chat + active flag
        $userWithMessages = $users->map(function ($user) use ($authUser) {
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

            $user->last_chat = $lastChat;

            // Active check (within 5 minutes)
            $user->is_active = $user->last_activity_at && $user->last_activity_at->gt(now()->subMinutes(5));

            return $user;
        });

        // Sort by last chat
        $sortedUsers = $userWithMessages->sortByDesc(function ($user) {
            return optional($user->last_chat)->created_at;
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved Successfully',
            'data' => ['users' => $sortedUsers],
        ], 200);
    }


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
            $file = Helper::fileUpload($request->file('file'),  'chat', time() . '_' . getFileName($request->file('file')));
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
            'sender:id,f_name,l_name,avatar,last_activity_at',
            'receiver:id,f_name,l_name,avatar,last_activity_at',
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
                'sender:id,f_name,l_name,avatar,last_activity_at',
                'receiver:id,f_name,l_name,avatar,last_activity_at',
                'room:id,user_one_id,user_two_id',
            ])
            ->orderBy('created_at')
            ->paginate($perPage);

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
            'receiver' => User::select('id', 'f_name', 'l_name', 'avatar', 'last_activity_at')
                ->where('id', $receiver_id)
                ->first(),
            'sender' => User::select('id', 'f_name', 'l_name', 'avatar', 'last_activity_at')
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

        $room = Room::with(['userOne:id , f_name , l_name , email , avatar , last_activity_at', 'userTwo: id , f_name , l_name , avatar , last_activity_at'])
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
        $users = User::select('id', 'f_name', 'l_name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where('f_name', 'LIKE', "%{$keyword}%")->orWhere('l_name', 'LIKE', "%{$keyword}%")->orWhere('email', 'LIKE', "%{$keyword}%")
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
}
