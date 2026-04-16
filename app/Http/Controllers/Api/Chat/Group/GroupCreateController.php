<?php

namespace App\Http\Controllers\Api\Chat\Group;

use App\Models\Group;
use App\Helper\Helper;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ChatGroupResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupDetailsResource;

class GroupCreateController extends Controller
{
    /**
     * Create a new group
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|max:5120',
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
     * Update group info (Admin only)
     */
    public function updateGroup(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:5120',
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

    /**
     * Update avatar (Only owner and admin)
     */
    public function updateAvatar(Request $request, $group_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code' => 422
            ], 422);
        }

        $authUser = Auth::guard('api')->user();
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'code' => 404
            ], 404);
        }

        // Only admins or group creator can update avatar
        if (!$group->isAdmin($authUser->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update group avatar',
                'code' => 403
            ], 403);
        }

        // Upload new avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_group_avatar.' . $file->getClientOriginalExtension();
            $newAvatar = Helper::fileUpload($file, 'groups', $fileName);

            // Delete old avatar if exists
            if (!empty($group->avatar)) {
                Helper::fileDelete($group->avatar);
            }

            // Update database
            $group->update(['avatar' => $newAvatar]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Group avatar updated successfully',
            'data' => new MessageResource($group),
            'code' => 200
        ]);
    }
}
