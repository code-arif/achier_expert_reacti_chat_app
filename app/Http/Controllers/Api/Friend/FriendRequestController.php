<?php

namespace App\Http\Controllers\Api\Friend;

use Exception;
use App\Models\Friend;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FriendRequestResource;

class FriendRequestController extends Controller
{
    use ApiResponse;

    /**
     * Send a friend request
     */
    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $sender = auth('api')->user();
        $receiverId = $request->receiver_id;

        if ($sender->id == $receiverId) {
            return $this->error([], 'You cannot send a friend request to yourself.', 400);
        }

        // Check existing request
        $existing = FriendRequest::where(function ($q) use ($sender, $receiverId) {
            $q->where('sender_id', $sender->id)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($sender, $receiverId) {
            $q->where('sender_id', $receiverId)
                ->where('receiver_id', $sender->id);
        })->first();

        if ($existing) {
            return $this->error([], 'Friend request already exists.', 409);
        }

        try {
            DB::beginTransaction();

            FriendRequest::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'status' => 'pending',
            ]);

            DB::commit();

            return $this->success([], 'Friend request sent successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel a sent friend request
     */
    public function cancelRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        $requestData = FriendRequest::where('sender_id', $user->id)
            ->where('receiver_id', $request->receiver_id)
            ->where('status', 'pending')
            ->first();

        if (!$requestData) {
            return $this->error([], 'No pending friend request found to cancel.', 404);
        }

        try {
            $requestData->delete();
            return $this->success([], 'Friend request canceled successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Accept a friend request
     */
    public function acceptRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $receiver = auth('api')->user();

        $friendRequest = FriendRequest::where('sender_id', $request->sender_id)
            ->where('receiver_id', $receiver->id)
            ->where('status', 'pending')
            ->first();

        if (!$friendRequest) {
            return $this->error([], 'Friend request not found.', 404);
        }

        try {
            DB::beginTransaction();

            // Update request status
            $friendRequest->update([
                'status' => 'accepted',
                'accepted_at' => Carbon::now(),
            ]);

            // Create friendship (both sides)
            Friend::create([
                'user_id' => $receiver->id,
                'friend_id' => $request->sender_id,
                'became_friends_at' => Carbon::now(),
            ]);

            Friend::create([
                'user_id' => $request->sender_id,
                'friend_id' => $receiver->id,
                'became_friends_at' => Carbon::now(),
            ]);

            DB::commit();

            return $this->success([], 'Friend request accepted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Decline a friend request
     */
    public function declineRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $receiver = auth('api')->user();

        $friendRequest = FriendRequest::where('sender_id', $request->sender_id)
            ->where('receiver_id', $receiver->id)
            ->where('status', 'pending')
            ->first();

        if (!$friendRequest) {
            return $this->error([], 'Friend request not found.', 404);
        }

        try {
            $friendRequest->update([
                'status' => 'declined',
                'declined_at' => Carbon::now(),
            ]);

            return $this->success([], 'Friend request declined.');
        } catch (Exception $e) {
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all friend requests for the logged-in user
     */
    public function getRequests()
    {
        $user = auth('api')->user();

        $requests = FriendRequest::with('sender:id,first_name,last_name,avatar,username')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->get();

        // return $this->success($requests, 'Friend requests fetched successfully.');
        return $this->success(
            FriendRequestResource::collection($requests),
            'Friend requests fetched successfully.'
        );
    }
}
