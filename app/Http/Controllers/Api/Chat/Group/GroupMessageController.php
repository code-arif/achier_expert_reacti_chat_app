<?php

namespace App\Http\Controllers\Api\Chat\Group;

use App\Models\Group;
use App\Helper\Helper;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use App\Models\GroupMessageRead;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\GroupMessageSendEvent;
use App\Models\GroupMessageUserStatus;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupMessageMediaResource;

class GroupMessageController extends Controller
{
    /**
     * Send message to group
     */
    public function sendMessage(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable|max:51200',
            'message_type' => 'nullable|in:normal,reaction',
            'reply_to_message_id' => 'nullable|exists:group_messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404]);
        }

        if (!$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403]);
        }

        // ---------------------------
        // FILE UPLOAD
        // ---------------------------
        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'group_message', time() . 'group_chat_image' . $request->file('file'));
        }

        // ---------------------------
        // DETERMINE MESSAGE TYPE
        // ---------------------------
        $messageType = $request->input('message_type', 'normal');
        $isBlurred = false;

        // If it's a normal message with media, it should be blurred
        if ($messageType === 'normal' && $file) {
            $isBlurred = true;
        }

        // ---------------------------
        // SAVE MESSAGE (only message-level data)
        // ---------------------------
        $message = GroupMessage::create([
            'group_id' => $group_id,
            'sender_id' => $authUser->id,
            'text' => $request->text,
            'file' => $file,
            'status' => 'sent',
            'message_type' => $messageType,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);

        // ---------------------------
        // SAVE USER-SPECIFIC STATUS FOR SENDER
        // ---------------------------
        // sender's own message → always unblurred
        GroupMessageUserStatus::create([
            'message_id' => $message->id,
            'user_id' => $authUser->id,
            'is_viewed' => false,
            'is_blurred' => $isBlurred,
        ]);

        // ---------------------------
        // PRE-LOAD RELATIONS
        // ---------------------------
        // $message->load([
        //     'sender:id,first_name,last_name,avatar,last_activity_at',
        //     'group:id,name,avatar'
        // ]);

        $message->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'group:id,name,avatar',
            'replyTo.sender:id,first_name,last_name,avatar', // ✅ নতুন
        ]);

        // ---------------------------
        // BROADCAST TO GROUP MEMBERS
        // ---------------------------
        broadcast(new GroupMessageSendEvent($message))->toOthers();

        // ---------------------------
        // RETURN SAME RESPONSE FORMAT
        // ---------------------------
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => ['message' => new MessageResource($message)],
            'code' => 200
        ]);
    }


    /**
     * Edit/Update group message
     */
    public function editMessage(Request $request, $group_id, $message_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403], 403);
        }

        $message = GroupMessage::where('id', $message_id)
            ->where('group_id', $group_id)
            ->where('sender_id', $authUser->id)
            ->first();

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found or you cannot edit this message', 'code' => 404], 404);
        }

        $message->text = $request->text;
        $message->save();

        $message->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'group:id,name,avatar'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => ['message' => new MessageResource($message)],
            'code' => 200
        ]);
    }

    /**
     * Get group messages with pagination
     */
    public function getMessages($group_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403], 403);
        }

        $perPage = 10000000;
        // $messages = GroupMessage::where('group_id', $group_id)
        //     ->with([
        //         'sender:id,first_name,last_name,avatar,last_activity_at',
        //         'reads.user:id,first_name,last_name',
        //         'messageStatus'
        //     ])
        //     ->orderBy('created_at', 'asc')
        //     ->paginate($perPage);

        $messages = GroupMessage::where('group_id', $group_id)
            ->with([
                'sender:id,first_name,last_name,avatar,last_activity_at',
                'reads.user:id,first_name,last_name',
                'messageStatus',
                'replyTo.sender:id,first_name,last_name,avatar', // ✅ নতুন
            ])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => [
                'messages' => MessageResource::collection($messages),
                'pagination' => [
                    'total' => $messages->total(),
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                ],
            ],
            'code' => 200
        ]);
    }


    /**
     * Get all message media
     */
    public function messageMedia($group_id)
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403], 403);
        }

        $perPage = 50;
        $messages = GroupMessage::where('group_id', $group_id)
            ->whereNotNull('file')
            ->with([
                'sender:id,first_name,last_name,avatar,last_activity_at',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Media files retrieved successfully',
            'data' => [
                'media' => GroupMessageMediaResource::collection($messages->items()),
                'pagination' => [
                    'total'       => $messages->total(),
                    'current_page' => $messages->currentPage(),
                    'last_page'   => $messages->lastPage(),
                    'per_page'    => $messages->perPage(),
                ],
            ],
            'code' => 200
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($group_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group || !$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'Group not found or access denied', 'code' => 403], 403);
        }

        $unreadMessages = GroupMessage::where('group_id', $group_id)
            ->whereDoesntHave('reads', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id);
            })
            ->where('sender_id', '!=', $authUser->id)
            ->pluck('id');

        foreach ($unreadMessages as $messageId) {
            GroupMessageRead::firstOrCreate([
                'group_message_id' => $messageId,
                'user_id' => $authUser->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
            'code' => 200
        ]);
    }

    /**
     * Mark message as viewed
     */
    public function markAsViewed(Request $request, $message_id): JsonResponse
    {
        $user_id = Auth::id();

        $message = GroupMessage::find($message_id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'code' => 404
            ]);
        }

        // Ensure user belongs to the group
        if (!$message->group->isMember($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this group',
                'code' => 403
            ]);
        }

        // Create or update user-specific status record
        $status = GroupMessageUserStatus::updateOrCreate(
            [
                'message_id' => $message_id,
                'user_id' => $user_id,
            ],
            [
                'is_viewed' => true,
                'is_blurred' => false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Message marked as viewed',
            'data' => ['status' => $status],
            'code' => 200
        ]);
    }


    /**
     * Bulk delete messages (Admin only)
     */
    public function deleteMessages(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'exists:group_messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isAdmin($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'Only admins can delete messages', 'code' => 403], 403);
        }

        GroupMessage::whereIn('id', $request->message_ids)
            ->where('group_id', $group_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Messages deleted successfully',
            'code' => 200
        ]);
    }
}
