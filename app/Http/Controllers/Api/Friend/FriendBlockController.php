<?php

namespace App\Http\Controllers\Api\Friend;

use Exception;
use App\Models\Friend;
use App\Models\BlockedUser;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FriendBlockController extends Controller
{
    use ApiResponse;

    /**
     * Block a user
     */
    public function blockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();
        $blockedUserId = $request->blocked_user_id;

        if ($user->id == $blockedUserId) {
            return $this->error([], 'You cannot block yourself.', 400);
        }

        // Check if already blocked
        $existing = BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $blockedUserId)
            ->first();

        if ($existing) {
            return $this->error([], 'User is already blocked.', 409);
        }

        try {
            DB::beginTransaction();

            // Remove any existing friend request or friendship
            FriendRequest::where(function ($q) use ($user, $blockedUserId) {
                $q->where('sender_id', $user->id)
                    ->where('receiver_id', $blockedUserId);
            })->orWhere(function ($q) use ($user, $blockedUserId) {
                $q->where('sender_id', $blockedUserId)
                    ->where('receiver_id', $user->id);
            })->delete();

            Friend::where(function ($q) use ($user, $blockedUserId) {
                $q->where('user_id', $user->id)
                    ->where('friend_id', $blockedUserId);
            })->orWhere(function ($q) use ($user, $blockedUserId) {
                $q->where('user_id', $blockedUserId)
                    ->where('friend_id', $user->id);
            })->delete();

            // Add to blocked users
            BlockedUser::create([
                'user_id' => $user->id,
                'blocked_user_id' => $blockedUserId,
                'reason' => $request->reason,
                'description' => $request->description,
            ]);

            DB::commit();

            return $this->success([], 'User has been blocked successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unblock a user
     */
    public function unblockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        $blockedUser = BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->first();

        if (!$blockedUser) {
            return $this->error([], 'User is not blocked.', 404);
        }

        try {
            $blockedUser->delete();
            return $this->success([], 'User has been unblocked successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get list of blocked users
     */
    public function blockedUsers()
    {
        $user = auth('api')->user();

        $blockedUsers = BlockedUser::with('blockedUser:id,first_name,last_name,username,avatar')
            ->where('user_id', $user->id)
            ->get(['id', 'blocked_user_id', 'reason', 'description', 'created_at']);

        return $this->success($blockedUsers, 'Blocked users fetched successfully.');
    }
}
