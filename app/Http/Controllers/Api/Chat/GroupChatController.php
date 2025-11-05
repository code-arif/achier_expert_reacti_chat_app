<?php

namespace App\Http\Controllers\Api\Chat;

use App\Models\User;
use App\Models\Group;
use App\Helper\Helper;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use App\Models\GroupMessageRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\GroupMessageSendEvent;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ChatGroupResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupDetailsResource;

class GroupChatController extends Controller
{
    /**
     * Create a new group
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('api')->user();

        // Upload avatar if exists
        $avatar = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_group_avatar.' . $file->getClientOriginalExtension();
            $avatar = Helper::fileUpload($file, 'groups', $fileName);
        }

        DB::beginTransaction();
        try {
            // Create group
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'avatar' => $avatar,
                'created_by' => $authUser->id
            ]);

            // Add creator as admin
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $authUser->id,
                'role' => 'admin'
            ]);

            // Add other members
            $members = array_filter($request->members, fn($id) => $id != $authUser->id);
            foreach ($members as $memberId) {
                GroupMember::create([
                    'group_id' => $group->id,
                    'user_id' => $memberId,
                    'role' => 'member'
                ]);
            }

            DB::commit();

            $group->load(['creator:id,first_name,last_name,avatar', 'members.user:id,first_name,last_name,avatar,last_activity_at']);

            return response()->json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => new ChatGroupResource($group),
                'code' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Get all groups for authenticated user
     */
    public function listGroups(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $keyword = $request->get('keyword');

        $groupsQuery = Group::whereHas('members', function ($query) use ($authUser) {
            $query->where('user_id', $authUser->id);
        });

        if ($keyword) {
            $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
        }

        $groups = $groupsQuery->with([
            'creator:id,first_name,last_name,avatar',
            'members.user:id,first_name,last_name,avatar,last_activity_at'
        ])->get();

        // Add last message and unread count
        $groups->map(function ($group) use ($authUser) {
            $group->last_message = $group->messages()->latest()->first();
            $group->unread_count = $group->messages()
                ->whereDoesntHave('reads', function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id);
                })
                ->where('sender_id', '!=', $authUser->id)
                ->count();
            $group->member_count = $group->members->count();
            return $group;
        });

        return response()->json([
            'success' => true,
            'message' => 'Groups retrieved successfully',
            'groups' => ChatGroupResource::collection($groups),
            'code' => 200
        ]);
    }

    /**
     * Get group details
     */
    public function groupDetails($group_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();

        $group = Group::with([
            'creator:id,first_name,last_name,avatar,email',
            'members.user:id,first_name,last_name,avatar,email,last_activity_at'
        ])->find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        // Check if user is member
        if (!$group->isMember($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403], 403);
        }

        $group->is_admin = $group->isAdmin($authUser->id);
        $group->member_count = $group->members->count();

        // Add full path for avatar
        if ($group->avatar) {
            $group->avatar_url = url('/' . $group->avatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Group details retrieved successfully',
            'data' => [
                'group' => new GroupDetailsResource($group)
            ],
            'code' => 200
        ]);
    }

    /**
     * Send message to group
     */
    public function sendMessage(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable|max:51200'
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

        $file = null;
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fileName = time() . '_group_message.' . $uploadedFile->getClientOriginalExtension();
            $file = Helper::fileUpload($uploadedFile, 'group_chat', $fileName);
        }

        $message = GroupMessage::create([
            'group_id' => $group_id,
            'sender_id' => $authUser->id,
            'text' => $request->text,
            'file' => $file
        ]);

        // Mark as read by sender
        GroupMessageRead::create([
            'group_message_id' => $message->id,
            'user_id' => $authUser->id
        ]);

        $message->load([
            'sender:id,first_name,last_name,avatar,last_activity_at',
            'group:id,name,avatar'
        ]);

        // Broadcast to group
        broadcast(new GroupMessageSendEvent($message))->toOthers();

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

        $perPage = 50;
        $messages = GroupMessage::where('group_id', $group_id)
            ->with([
                'sender:id,first_name,last_name,avatar,last_activity_at',
                'reads.user:id,first_name,last_name'
            ])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => [
                'messages' => $messages->items(),
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
     * Add members to group (Admin only)
     */
    public function addMembers(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
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
            return response()->json(['success' => false, 'message' => 'Only admins can add members', 'code' => 403], 403);
        }

        $addedMembers = [];
        foreach ($request->members as $memberId) {
            if (!$group->isMember($memberId)) {
                GroupMember::create([
                    'group_id' => $group_id,
                    'user_id' => $memberId,
                    'role' => 'member'
                ]);
                $addedMembers[] = $memberId;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Members added successfully',
            'data' => ['added_members' => $addedMembers],
            'code' => 200
        ]);
    }

    /**
     * Remove member from group (Admin only)
     */
    public function removeMember($group_id, $user_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isAdmin($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'Only admins can remove members', 'code' => 403], 403);
        }

        // Cannot remove creator
        if ($user_id == $group->created_by) {
            return response()->json(['success' => false, 'message' => 'Cannot remove group creator', 'code' => 403], 403);
        }

        GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
            'code' => 200
        ]);
    }

    /**
     * Make member admin (Admin only)
     */
    public function makeAdmin($group_id, $user_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if (!$group->isAdmin($authUser->id)) {
            return response()->json(['success' => false, 'message' => 'Only admins can promote members', 'code' => 403], 403);
        }

        $member = GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'User is not a member', 'code' => 404], 404);
        }

        $member->update(['role' => 'admin']);

        return response()->json([
            'success' => true,
            'message' => 'Member promoted to admin',
            'code' => 200
        ]);
    }

    /**
     * Leave group
     */
    public function leaveGroup($group_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        // Creator cannot leave, must delete group
        if ($authUser->id == $group->created_by) {
            return response()->json(['success' => false, 'message' => 'Group creator cannot leave. Delete the group instead.', 'code' => 403], 403);
        }

        GroupMember::where('group_id', $group_id)->where('user_id', $authUser->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Left group successfully',
            'code' => 200
        ]);
    }

    /**
     * Delete group (Creator only)
     */
    public function deleteGroup($group_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

        if ($authUser->id != $group->created_by) {
            return response()->json(['success' => false, 'message' => 'Only group creator can delete the group', 'code' => 403], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully',
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


    /**
     * Update group info (Admin only)
     */
    public function updateGroup(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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
            return response()->json(['success' => false, 'message' => 'Only admins can update group', 'code' => 403], 403);
        }

        if ($request->name) {
            $group->name = $request->name;
        }

        if ($request->has('description')) {
            $group->description = $request->description;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_group_avatar.' . $file->getClientOriginalExtension();
            $avatar = Helper::fileUpload($file, 'groups', $fileName);
            $group->avatar = $avatar;
        }

        $group->save();

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully',
            'data' =>  new MessageResource($group),
            'code' => 200
        ]);
    }
}
