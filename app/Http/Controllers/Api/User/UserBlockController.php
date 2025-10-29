<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\Friend;
use App\Models\UserBlock;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BlockedUserResource;
use App\Http\Resources\BlockedUserCollection;

class UserBlockController extends Controller
{
    use ApiResponse;

    /**
     * Toggle Block / Unblock
     */
    public function toggleBlock($block_user_id)
    {
        // Validate URL param
        $validator = Validator::make(['block_user_id' => $block_user_id], [
            'block_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], 'User not found.', 404);
        }

        $user = auth('api')->user();

        if ($user->id == $block_user_id) {
            return $this->error([], 'You cannot block yourself.', 400);
        }

        // Check if already blocked
        $existing = UserBlock::where('user_id', $user->id)
            ->where('block_user_id', $block_user_id)
            ->first();

        try {
            DB::beginTransaction();

            if ($existing) {
                // UNBLOCK
                $existing->delete();
                DB::commit();
                return $this->success([], 'User has been unblocked.');
            }

            // BLOCK
            // Remove friend requests
            FriendRequest::where(function ($q) use ($user, $block_user_id) {
                $q->where('sender_id', $user->id)->where('receiver_id', $block_user_id);
            })->orWhere(function ($q) use ($user, $block_user_id) {
                $q->where('sender_id', $block_user_id)->where('receiver_id', $user->id);
            })->delete();

            // Remove friendship
            Friend::where(function ($q) use ($user, $block_user_id) {
                $q->where('user_id', $user->id)->where('friend_id', $block_user_id);
            })->orWhere(function ($q) use ($user, $block_user_id) {
                $q->where('user_id', $block_user_id)->where('friend_id', $user->id);
            })->delete();

            // Create block
            UserBlock::create([
                'user_id'       => $user->id,
                'block_user_id' => $block_user_id,
            ]);

            DB::commit();
            return $this->success([], 'User has been blocked.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong.', 500);
        }
    }

    /**
     * List of blocked users
     */
    public function blockedUsers(Request $request)
    {
        $user = auth('api')->user();

        $perPage = $request->get('per_page', 10); // ?per_page=15

        $blockedUsers = UserBlock::with('blockedUser:id,first_name,last_name,username,avatar')
            ->where('user_id', $user->id)
            ->select(['id', 'block_user_id', 'created_at'])
            ->paginate($perPage);

        return $this->success(
            new BlockedUserCollection($blockedUsers),
            'Blocked users fetched successfully.'
        );
    }
}
