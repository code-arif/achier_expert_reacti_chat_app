<?php

namespace App\Http\Controllers\Api\React\Post;

use App\Models\Like;
use App\Models\Post;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

class PostLikeController extends Controller
{
    use ApiResponse;

    //like and unlike post
    public function toggleLike($postId)
    {
        $user = auth('api')->user();

        // Check if the post exists
        $model = Post::find($postId);
        if (!$model) {
            return $this->error([], 'Post not found.', 404);
        }

        // Check if the user already liked the post
        $alreadyLiked = Like::where([
            ['likeable_id', $model->id],
            ['likeable_type', get_class($model)],
            ['user_id', $user->id]
        ])->first();

        if ($alreadyLiked) {
            // Unlike the post
            $alreadyLiked->delete();
            $model->decrement('like_count');

            return $this->success([
                'post_id'    => $model->id,
                'user_id'    => $user->id,
                'status'     => 'Unliked',
                'type'       => 'Post',
                'like_count' => $model->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Disliked successfully.', 200);
        } else {
            // Like the post
            Like::create([
                'likeable_id' => $postId,
                'user_id'     => $user->id,
                'likeable_type' => Post::class
            ]);

            $model->increment('like_count');

            return $this->success([
                'post_id'    => $model->id,
                'user_id'    => $user->id,
                'status'     => 'Liked',
                'type'       => 'Post',
                'like_count' => $model->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Liked successfully.', 200);
        }
    }


    //get all likes of a posts
    public function index($postId)
    {
        $post = Post::with('likes.user')->find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $likeUsers = $post->likes->map(function ($like) {
            return [
                'id'     => optional($like->user)->id,
                'name'   => optional($like->user)->f_name . ' ' . optional($like->user)->l_name,
                'avatar' => optional($like->user)->avatar,
            ];
        });


        return $this->success($likeUsers, 'Post like list retrieved successfully.', 200);
    }
}
