<?php

namespace App\Http\Controllers\Api\React\Event;

use App\Models\Like;
use App\Models\Event;
use App\Models\PostLike;
use App\Models\eventLike;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventLikeController extends Controller
{
    use ApiResponse;

    //like and unlike event
    public function toggleLike($eventId)
    {
        $user = auth('api')->user();

        // Check if the post exists
        $model = Event::find($eventId);
        if (!$model) {
            return $this->error([], 'Event not found.', 404);
        }

        // Check if the user already liked the post
        $alreadyLiked = Like::where([
            ['likeable_id', $model->id],
            ['likeable_type', get_class($model)],
            ['user_id', $user->id]
        ])->first();

        if ($alreadyLiked) {
            // Unlike the event
            $alreadyLiked->delete();
            $model->decrement('like_count');

            return $this->success([
                'event_id'    => $model->id,
                'user_id'    => $user->id,
                'status'     => 'Unliked',
                'type'       => 'Event',
                'like_count' => $model->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Unliked successfully.', 200);
        } else {
            // Like the post
            Like::create([
                'likeable_id' => $eventId,
                'user_id'     => $user->id,
                'likeable_type' => Event::class
            ]);

            $model->increment('like_count');

            return $this->success([
                'post_id'    => $model->id,
                'user_id'    => $user->id,
                'status'     => 'Liked',
                'type'       => 'Event',
                'like_count' => $model->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Liked successfully.', 200);
        }
    }


    //get all likes
    public function index($eventId)
    {
        $event = Event::with('likes.user')->find($eventId);

        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $likeUsers = $event->likes->map(function ($like) {
            return [
                'id' => $like->user->id,
                'name' => $like->user->f_name . ' ' . $like->user->l_name,
                'avatar' => $like->user->avatar
            ];
        });

        return $this->success($likeUsers, 'Event like list retrieved successfully.', 200);
    }
}
