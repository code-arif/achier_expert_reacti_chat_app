<?php

namespace App\Http\Controllers\Api\React\User;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\FollowNotification;

class FollowerController extends Controller
{
    use ApiResponse;

    // Follow or Unfollow a user
    public function toggleFollow($id)
    {
        $authUser = auth('api')->user();

        if ($authUser->id == $id) {
            return $this->error([], "You can't follow yourself.", 403);
        }

        $userToFollow = User::find($id);
        if (!$userToFollow) {
            return $this->error([], "User not found.", 404);
        }

        $isFollowing = $authUser->followings()->where('following_id', $id)->exists();

        if ($isFollowing) {
            // Unfollow
            $authUser->followings()->detach($id);
            $message = 'Unfollowed successfully.';
        } else {
            // Follow
            $authUser->followings()->attach($id);
            $message = 'Followed successfully.';

            // Send notification to the user being followed
            try {
                if ($userToFollow->id !== $authUser->id) {
                    $userToFollow->notify(new FollowNotification($authUser));
                }
            } catch (Exception $e) {
                Log::error('Follow notification error: ' . $e->getMessage());
            }
        }

        return $this->success([], $message);
    }

    // Get followers of authenticated user
    public function getFollowers(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $perPage = $request->input('per_page', 10);

        $followers = $user->followers()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->paginate($perPage);

        // Transform follower data
        $followersData = $followers->getCollection()->transform(function ($follower) {
            return [
                'id'     => $follower->id,
                'name'   => trim($follower->f_name . ' ' . $follower->l_name),
                'profession' => $follower->profession ?? '',
                'avatar' => $follower->avatar,
            ];
        });

        return $this->success([
            'count'      => $followers->total(),
            'followers'  => $followersData,
            'pagination' => [
                'total'        => $followers->total(),
                'current_page' => $followers->currentPage(),
                'last_page'    => $followers->lastPage(),
                'per_page'     => $followers->perPage(),
            ],
        ], 'Followers retrieved successfully.');
    }


    // Get followers of any user by user id
    public function getUserFollowers(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $perPage = $request->input('per_page', 10);

        $followers = $user->followers()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->paginate($perPage);

        // Transform follower data
        $followersData = $followers->getCollection()->transform(function ($follower) {
            return [
                'id'     => $follower->id,
                'name'   => trim($follower->f_name . ' ' . $follower->l_name),
                'profession' => $follower->profession ?? '',
                'avatar' => $follower->avatar,
            ];
        });

        return $this->success([
            'count'      => $followers->total(),
            'followers'  => $followersData,
            'pagination' => [
                'total'        => $followers->total(),
                'current_page' => $followers->currentPage(),
                'last_page'    => $followers->lastPage(),
                'per_page'     => $followers->perPage(),
            ],
        ], 'Followers retrieved successfully.');
    }


    // Get followings of authenticated user
    public function getFollowings(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $perPage = $request->input('per_page', 10);

        $followings = $user->followings()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->paginate($perPage);

        // Transform following data
        $followingsData = $followings->getCollection()->transform(function ($following) {
            return [
                'id'     => $following->id,
                'name'   => trim($following->f_name . ' ' . $following->l_name),
                'profession' => $following->profession ?? '',
                'avatar' => $following->avatar,
            ];
        });

        return $this->success([
            'count'      => $followings->total(),
            'followings' => $followingsData,
            'pagination' => [
                'total'        => $followings->total(),
                'current_page' => $followings->currentPage(),
                'last_page'    => $followings->lastPage(),
                'per_page'     => $followings->perPage(),
            ],
        ], 'Followings retrieved successfully.');
    }


    // Get followings of any user
    public function getUserFollowings(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $perPage = $request->input('per_page', 10);

        $followings = $user->followings()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->paginate($perPage);

        // Transform following data
        $followingsData = $followings->getCollection()->transform(function ($following) {
            return [
                'id'     => $following->id,
                'name'   => trim($following->f_name . ' ' . $following->l_name),
                'profession' => $following->profession ?? '',
                'avatar' => $following->avatar,
            ];
        });

        return $this->success([
            'count'      => $followings->total(),
            'followings' => $followingsData,
            'pagination' => [
                'total'        => $followings->total(),
                'current_page' => $followings->currentPage(),
                'last_page'    => $followings->lastPage(),
                'per_page'     => $followings->perPage(),
            ],
        ], 'Followings retrieved successfully.');
    }


    // Get auth user friends (mutuals)
    public function getFriends(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return $this->error([], 'User not found.', 404);
        }

        $perPage = $request->input('per_page', 10);

        // Get all user IDs the auth user is following
        $followingIds = $authUser->followings()->pluck('users.id')->toArray();

        // Get all user IDs who follow the auth user
        $followerIds = $authUser->followers()->pluck('users.id')->toArray();

        // Find mutuals (friends = intersection)
        $friendIds = array_intersect($followingIds, $followerIds);

        // Retrieve friends with pagination
        $friends = User::whereIn('id', $friendIds)
            ->select('id', 'f_name', 'l_name', 'avatar')
            ->paginate($perPage);

        // Transform data
        $friendsData = $friends->getCollection()->transform(function ($friend) {
            return [
                'id'     => $friend->id,
                'name'   => trim($friend->f_name . ' ' . $friend->l_name),
                'profession' => $friend->profession ?? '',
                'avatar' => $friend->avatar,
            ];
        });

        return $this->success([
            'count'    => $friends->total(),
            'friends'  => $friendsData,
            'pagination' => [
                'total'        => $friends->total(),
                'current_page' => $friends->currentPage(),
                'last_page'    => $friends->lastPage(),
                'per_page'     => $friends->perPage(),
            ],
        ], 'Friends retrieved successfully.');
    }
}
