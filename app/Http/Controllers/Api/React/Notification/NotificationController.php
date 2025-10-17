<?php

namespace App\Http\Controllers\Api\React\Notification;

use Exception;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use ApiResponse;

    // get all notifications
    public function allNotifications()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Fetch unread notifications
            $notifications = $user->notifications()
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $count = $notifications->count();

            $notifications = $notifications->map(function ($notification) {
                $typeMap = [
                    'App\\Notifications\\PostCreateNotification' => 'post',
                    'App\\Notifications\\EventCreateNotification' => 'event',
                    'App\\Notifications\\EventReplyCommentNotification' => 'event_comment_reply',
                    'App\\Notifications\\FollowNotification' => 'follow',
                    'App\\Notifications\\PostReplyCommentNotification' => 'post_comment_reply',
                ];

                $typeLabel = $typeMap[$notification->type] ?? 'unknown';

                return [
                    'id'          => $notification->id,
                    'data'        => $notification->data,
                    'type'        => $typeLabel,
                    'read_at'     => $notification->read_at,
                    'created_at'  => $notification->created_at->diffForHumans(),
                ];
            });

            return $this->success([
                'count'         => $count,
                'notifications' => $notifications,
            ], 'Unread notifications retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::error('Notification fetch error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong.', 500);
        }
    }

    //mark as read specific notification
    public function readNotification($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();

            if (!$notification) {
                return $this->error([], 'Notification not found.', 404);
            }

            $notification->markAsRead();

            return $this->success([], 'Notification marked as read successfully.', 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    //mark as read all notification
    public function readAllNotifications()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $user->unreadNotifications->markAsRead();

            return $this->success([], 'All notifications marked as read successfully.', 200);
        } catch (Exception $e) {
            Log::error('Notification mark-all error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong.', 500);
        }
    }
}
