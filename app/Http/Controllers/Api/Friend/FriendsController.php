<?php

namespace App\Http\Controllers\Api\Friend;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\FriendListResource;

class FriendsController extends Controller
{
    use ApiResponse;

    // list of all auth user friend
    public function friendList()
    {
        $user = auth('api')->user();

        // friend IDs collect from both side
        $friendIds = DB::table('friends')
            ->where('user_id', $user->id)
            ->pluck('friend_id')
            ->merge(
                DB::table('friends')
                    ->where('friend_id', $user->id)
                    ->pluck('user_id')
            )
            ->unique()
            ->toArray();

        // users fatch with friendsIds
        $friends = User::whereIn('id', $friendIds)
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'phone', 'avatar')
            ->paginate(15);

        return $this->success(
            FriendListResource::collection($friends),
            'Friend list fetched successfully.'
        );
    }

    // list of another user's friend list
    public function userFriendList($userId)
    {
        // Check if user exists
        $profileUser = User::findOrFail($userId);

        // Optional: Check if blocked or privacy settings
        $currentUser = auth('api')->user();

        // Check if current user is blocked by profile user
        $isBlocked = DB::table('blocked_users')
            ->where('user_id', $userId)
            ->where('blocked_user_id', $currentUser->id)
            ->exists();

        if ($isBlocked) {
            return $this->error('You cannot view this user\'s friends.', 403);
        }

        // friend IDs collect from both side
        $friendIds = DB::table('friends')
            ->where('user_id', $userId)
            ->pluck('friend_id')
            ->merge(
                DB::table('friends')
                    ->where('friend_id', $userId)
                    ->pluck('user_id')
            )
            ->unique()
            ->toArray();

        // users fatch with friendsIds
        $friends = User::whereIn('id', $friendIds)
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'phone', 'avatar')
            ->paginate(15);

        return $this->success(
            FriendListResource::collection($friends),
            $profileUser->first_name . '\'s friend list fetched successfully.'
        );
    }
}
