<?php

namespace App\Http\Controllers\Api\Friend;

use Exception;
use App\Models\Friend;
use App\Models\BlockedUser;
use App\Traits\ApiResponse;
use App\Models\ReportedUser;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReportedUserResource;
use App\Http\Resources\ReportedUserCollection;

class ReportUserController extends Controller
{
    use ApiResponse;

    /**
     * Report a user (no toggle, one-time)
     */
    public function reportUser(Request $request, $reported_user_id)
    {
        // Validate URL param
        $validator = Validator::make(['reported_user_id' => $reported_user_id], [
            'reported_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], 'User not found.', 404);
        }

        // Validate body (optional fields)
        $validator = Validator::make($request->all(), [
            'reason'       => 'nullable|string|max:255',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        if ($user->id == $reported_user_id) {
            return $this->error([], 'You cannot report yourself.', 400);
        }

        // Check if already reported
        $existing = ReportedUser::where('user_id', $user->id)
            ->where('reported_user_id', $reported_user_id)
            ->exists();

        if ($existing) {
            return $this->error([], 'You have already reported this user.', 409);
        }

        try {
            DB::beginTransaction();

            // Remove friend requests
            FriendRequest::where(function ($q) use ($user, $reported_user_id) {
                $q->where('sender_id', $user->id)->where('receiver_id', $reported_user_id);
            })->orWhere(function ($q) use ($user, $reported_user_id) {
                $q->where('sender_id', $reported_user_id)->where('receiver_id', $user->id);
            })->delete();

            // Remove friendship
            Friend::where(function ($q) use ($user, $reported_user_id) {
                $q->where('user_id', $user->id)->where('friend_id', $reported_user_id);
            })->orWhere(function ($q) use ($user, $reported_user_id) {
                $q->where('user_id', $reported_user_id)->where('friend_id', $user->id);
            })->delete();

            // Create report
            ReportedUser::create([
                'user_id'          => $user->id,
                'reported_user_id' => $reported_user_id,
                'reason'           => $request->reason,
                'description'      => $request->description,
            ]);

            DB::commit();

            return $this->success([], 'User has been reported successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong.', 500);
        }
    }

    /**
     * List of reported users
     */
    public function reportedUsers(Request $request)
    {
        $user = auth('api')->user();

        $perPage = $request->get('per_page', 10); // ?per_page=20

        $reports = ReportedUser::with('reportedUser:id,first_name,last_name,username,avatar')
            ->where('user_id', $user->id)
            ->select(['id', 'reported_user_id', 'reason', 'description', 'created_at'])
            ->paginate($perPage);

        return $this->success(
            new ReportedUserCollection($reports),
            'Reported users fetched successfully.'
        );
    }
}
