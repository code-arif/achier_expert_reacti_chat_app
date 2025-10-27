<?php

namespace App\Http\Controllers\Web\Backend;

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

class AdminGroupChatController extends Controller
{
    /**
     * Show group chat page (for web interface)
     */
    public function index()
    {
        return view('backend.layouts.chat.group_chat');
    }

    /**
     * Get users list for group creation
     */
    public function getUsersList(Request $request): JsonResponse
    {
        $authUser = Auth::guard('web')->user();

        $users = User::where('id', '!=', $authUser->id)
            ->select('id', 'first_name', 'last_name', 'email', 'avatar')
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
            'code' => 200
        ]);
    }

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

        $authUser = Auth::guard('web')->user();

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
                'code' => 500
            ], 500);
        }
    }

    /**
     * Get all groups for authenticated user - FIXED VERSION
     */
    public function listGroups(Request $request): JsonResponse
    {
        try {
            $authUser = Auth::guard('web')->user();
            $keyword = $request->get('keyword');

            $groupsQuery = Group::whereHas('members', function ($query) use ($authUser) {
                $query->where('user_id', $authUser->id);
            });

            if ($keyword) {
                $groupsQuery->where('name', 'LIKE', "%{$keyword}%");
            }

            $groups = $groupsQuery->with([
                'creator:id,first_name,last_name,avatar',
                'members.user:id,first_name,last_name,avatar,last_activity_at',
                'messages' => function ($query) {
                    $query->latest()->first();
                }
            ])->get();

            // Add last message and unread count
            $groups->each(function ($group) use ($authUser) {
                // Get last message
                $lastMessage = $group->messages()->latest()->first();

                if ($lastMessage) {
                    $group->last_message = [
                        'id' => $lastMessage->id,
                        'text' => $lastMessage->text,
                        'file' => $lastMessage->file,
                        'created_at' => $lastMessage->created_at->toISOString(),
                        'relative_time' => $lastMessage->created_at->diffForHumans(),
                    ];
                } else {
                    $group->last_message = null;
                }

                // Calculate unread count
                $group->unread_count = $group->messages()
                    ->whereDoesntHave('reads', function ($q) use ($authUser) {
                        $q->where('user_id', $authUser->id);
                    })
                    ->where('sender_id', '!=', $authUser->id)
                    ->count();

                $group->member_count = $group->members->count();
            });

            return response()->json([
                'success' => true,
                'message' => 'Groups retrieved successfully',
                'groups' => ChatGroupResource::collection($groups),
                'code' => 200
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading groups: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load groups',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Get group details
     */
    public function groupDetails($group_id): JsonResponse
    {
        $authUser = Auth::guard('web')->user();

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
     * Send message to group - FIXED VERSION
     */
    public function sendMessage(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp3,wav,mp4,mov,avi,txt,pdf,doc,docx,xls,xlsx,zip,rar|max:51200'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $authUser = Auth::guard('web')->user();
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

        // Broadcast to all group members
        try {
            broadcast(new GroupMessageSendEvent($message))->toOthers();
            Log::info('Message broadcasted successfully', ['message_id' => $message->id]);
        } catch (\Exception $e) {
            Log::error('Broadcasting failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => ['message' => new MessageResource($message)],
            'code' => 200
        ]);
    }

    /**
     * Get group messages - FIXED VERSION
     */
    public function getMessages($group_id): JsonResponse
    {
        try {
            $authUser = Auth::guard('web')->user();
            $group = Group::find($group_id);

            if (!$group) {
                return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
            }

            if (!$group->isMember($authUser->id)) {
                return response()->json(['success' => false, 'message' => 'You are not a member of this group', 'code' => 403], 403);
            }

            $messages = GroupMessage::where('group_id', $group_id)
                ->with([
                    'sender:id,first_name,last_name,avatar,last_activity_at',
                    'reads.user:id,first_name,last_name'
                ])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Messages retrieved successfully',
                'data' => [
                    'messages' => MessageResource::collection($messages),
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load messages',
                'code' => 500
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($group_id): JsonResponse
    {
        $authUser = Auth::guard('web')->user();
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

        $authUser = Auth::guard('web')->user();
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
            'data' => new ChatGroupResource($group),
            'code' => 200
        ]);
    }

    /**
     * Leave group
     */
    public function leaveGroup($group_id): JsonResponse
    {
        $authUser = Auth::guard('web')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found', 'code' => 404], 404);
        }

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
        $authUser = Auth::guard('web')->user();
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
}
