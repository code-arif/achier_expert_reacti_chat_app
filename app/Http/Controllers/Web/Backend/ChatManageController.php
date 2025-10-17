<?php
namespace App\Http\Controllers\Web\Backend;

use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSendEvent;

class ChatManageController extends Controller
{
    public function index()
    {

        return view('backend.layouts.chat.index');
    }

    public function list(): JsonResponse
    {
        $authUser = Auth::user();

        // Fetch users who are connected as senders or receivers with the authenticated user
        $users = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->whereHas('senders', function ($query) use ($authUser) {
                $query->where('receiver_id', $authUser->id);
            })
            ->orWhereHas('receivers', function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id);
            })
            ->where('id', '!=', $authUser->id)
            ->get();

        // Append the last message for each user
        $usersWithMessages = $users->map(function ($user) use ($authUser) {
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
            return $user;
        });

        // Sort users by the last message's created_at timestamp in descending order
        $sortedUsers = $usersWithMessages->sortByDesc(function ($user) {
            return optional($user->last_chat)->created_at;
        })->values(); // Reset keys after sorting

        $data = [
            'users' => $sortedUsers,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data'    => $data,
        ], 200);
    }

    public function search(Request $request)
    {



        $user_id = Auth::id();

        $keyword = $request->get('keyword');
        $users   = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where('name', 'LIKE', "%{$keyword}%")->orWhere('email', 'LIKE', "%{$keyword}%")
            ->get();

        $data = [
            'users' => $users,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data'    => $data,
        ], 200);
    }

    /**
     ** Get messages between the authenticated user and another user
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function conversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::id();

        Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $chat = Chat::query()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })
            ->with([
                'sender:id,name,email,avatar,last_activity_at',
                'receiver:id,name,email,avatar,last_activity_at',
                'room:id,first_user_id,second_user_id',
            ])
            ->orderBy('created_at')
            ->limit(50)
            ->get();

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('first_user_id', $receiver_id)->where('second_user_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('first_user_id', $sender_id)->where('second_user_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'first_user_id' => $sender_id,
                'second_user_id' => $receiver_id,
            ]);
        }

        $data = [
            'receiver' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $receiver_id)->first(),
            'sender'   => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $sender_id)->first(),
            'room'     => $room,
            'chat'     => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    /**
     * Send a message to another user
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function send($receiver_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $sender_id = auth('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('first_user_id', $receiver_id)->where('second_user_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('first_user_id', $sender_id)->where('second_user_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'first_user_id' => $sender_id,
                'second_user_id' => $receiver_id,
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time() . '_' . getFileName($request->file('file')));
        }

        $chat = Chat::create([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'text'        => $request->text,
            'file'        => $file,
            'room_id'     => $room->id,
            'status'      => 'sent',
        ]);

        //* Load the sender's information
        $chat->load([
            'sender:id,name,email,avatar,last_activity_at',
            'receiver:id,name,email,avatar,last_activity_at',
            'room:id,first_user_id,second_user_id',
        ]);

        broadcast(new MessageSendEvent($chat));

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $chat = Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::id();

        $chat = Chat::where('id', $chat_id)->where('receiver_id', $sender_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    public function room($receiver_id)
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::with(['userOne:id,name,email,avatar,last_activity_at', 'userTwo:id,name,email,avatar,last_activity_at'])
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('first_user_id', $receiver_id)->where('second_user_id', $sender_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('first_user_id', $sender_id)->where('second_user_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'first_user_id' => $sender_id,
                'second_user_id' => $receiver_id,
            ]);
        }

        $data = [
            'room' => $room,
        ];

        return response()->json(['success' => true, 'message' => 'Group retrieved successfully', 'data' => $data, 'code' => 200]);
    }

}
