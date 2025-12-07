<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserListResource;

class UserController extends Controller
{
    use ApiResponse;

    // get user profile
    public function userDetais($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->error([], 'User not found.', 200);
            }
            return $this->success(new UserResource($user), 'User Profile Retrieved Successfully', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    // user list
    public function userList(Request $request)
    {
        try {
            $currentUser = auth('api')->user();

            if (!$currentUser) {
                return $this->error([], 'Unauthorized', 401);
            }

            // Preload friend IDs to avoid N+1
            $sent = DB::table('friends')
                ->where('user_id', $currentUser->id)
                ->select('friend_id as user_id');

            $received = DB::table('friends')
                ->where('friend_id', $currentUser->id)
                ->select('user_id');

            $friendIds = $sent->union($received)->pluck('user_id');

            // Preload sent friend request IDs
            $sentRequests = DB::table('friend_requests')
                ->where('sender_id', $currentUser->id)
                ->where('status', 'pending')
                ->pluck('receiver_id');

            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            $query = User::query()
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    });
                })
                ->where('id', '!=', $currentUser->id)
                ->select(['id', 'first_name', 'last_name', 'username', 'avatar']);

            $users = $query->paginate($perPage);

            // Add flags: is_friend & is_request_sent
            $users->getCollection()->transform(function ($user) use ($friendIds, $sentRequests) {
                $user->is_friend = $friendIds->contains($user->id);
                $user->is_request_sent = $sentRequests->contains($user->id);
                return $user;
            });

            return $this->success(
                new UserListResource($users),
                'Users retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
