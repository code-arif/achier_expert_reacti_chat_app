<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use App\Models\Event;
use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EventReplyCommentNotification;

class EventCommentController extends Controller
{
    use ApiResponse;

    // Store a new comment
    public function store(Request $request, $eventId)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'comment'    => 'required|string|max:1000',
            'parent_id'  => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        // Find the event
        $event = Event::find($eventId);
        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $user = auth()->guard('api')->user();

        // Create the comment
        $comment = new Comment([
            'comment'   => $request->comment,
            'user_id'   => $user->id,
            'parent_id' => $request->parent_id,
        ]);

        $event->comments()->save($comment);

        // Only increment event comment count if it's a top-level comment
        if (!$request->parent_id) {
            $event->increment('comment_count');
        }

        $comment->load('user');

        // Prepare response structure
        $response = [
            'id'            => $comment->id,
            'user_id'       => $comment->user->id,
            'comment'       => $comment->comment,
            'parent_id'     => $comment->parent_id,
            'comment_count' => $event->comment_count,
            'user'          => [
                'id'     => $comment->user->id,
                'name'   => trim($comment->user->f_name . ' ' . $comment->user->l_name),
                'avatar' => $comment->user->avatar,
            ],
        ];

        // Notify parent comment user if it's a reply
        try {
            if ($request->parent_id) {
                $parentComment = Comment::find($request->parent_id);

                if ($parentComment && $parentComment->user_id !== $user->id) {
                    $parentComment->user->notify(new EventReplyCommentNotification($user, $comment, $event));
                }
            }
        } catch (Exception $e) {
            Log::error('Reply notification error: ' . $e->getMessage());
        }

        return $this->success($response, 'Comment added successfully.', 201);
    }

    // List all top-level comments with replies
    public function index($eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $comments = Comment::with(['user', 'replies.user'])
            ->where('commentable_type', Event::class)
            ->where('commentable_id', $eventId)
            ->whereNull('parent_id')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id'         => $comment->id,
                    'comment'    => $comment->comment,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user'       => [
                        'id'     => $comment->user->id,
                        'name'   => trim($comment->user->f_name . ' ' . $comment->user->l_name),
                        'avatar' => $comment->user->avatar,
                    ],
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id'         => $reply->id,
                            'comment'    => $reply->comment,
                            'created_at' => $reply->created_at->diffForHumans(),
                            'user'       => [
                                'id'     => $reply->user->id,
                                'name'   => trim($reply->user->f_name . ' ' . $reply->user->l_name),
                                'avatar' => $reply->user->avatar,
                            ],
                        ];
                    }),
                ];
            });

        return $this->success($comments, 'Event comments with replies retrieved successfully.', 200);
    }

    // Update a comment
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $comment = Comment::with('user')->find($id);
        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth()->guard('api')->id()) {
            return $this->error([], 'Unauthorized to update this comment.', 403);
        }

        $comment->update(['comment' => $request->comment]);

        return $this->success([
            'id'        => $comment->id,
            'user_id'   => $comment->user->id,
            'comment'   => $comment->comment,
            'parent_id' => $comment->parent_id,
            'user'      => [
                'id'     => $comment->user->id,
                'name'   => trim($comment->user->f_name . ' ' . $comment->user->l_name),
                'avatar' => $comment->user->avatar,
            ],
        ], 'Comment updated successfully.', 200);
    }

    // Delete a comment
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth()->guard('api')->id()) {
            return $this->error([], 'Unauthorized to delete this comment.', 403);
        }

        $isRoot = is_null($comment->parent_id);
        $event = ($comment->commentable_type === Event::class)
            ? Event::find($comment->commentable_id)
            : null;

        $comment->delete();

        if ($event && $isRoot && $event->comment_count > 0) {
            $event->decrement('comment_count');
        }

        return $this->success([], 'Comment deleted successfully.', 200);
    }
}
