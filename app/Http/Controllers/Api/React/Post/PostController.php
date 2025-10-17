<?php

namespace App\Http\Controllers\Api\React\Post;

use Exception;
use App\Models\Post;
use App\Helper\Helper;
use App\Models\Hashtag;
use App\Models\PostImage;
use App\Models\PostVideo;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Post\PostCollection;
use App\Notifications\PostCreateNotification;

class PostController extends Controller
{
    use ApiResponse;

    /*store post
     * Create a new post with content, images, videos, and hashtags.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'content' => ['nullable', 'string'],
                'media.*' => ['nullable', 'file', 'max:51200'],
            ]);

            if ($validator->fails()) {
                return $this->error(['Validation failed'], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            // Create post
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $request->input('content'),
            ]);

            // Notify user when post is created
            try {
                $user->notify(new PostCreateNotification($post));
            } catch (Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }

            // Upload media files (image/video mixed)
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $mimeType = $file->getMimeType();

                    if (str_starts_with($mimeType, 'image/')) {
                        // Check extension & size for images
                        if (!in_array($file->extension(), ['jpg', 'jpeg', 'png', 'gif', 'webp']) || $file->getSize() > 5120 * 1024) {
                            continue;
                        }

                        $imagePath = Helper::uploadImage($file, 'posts/images');
                        PostImage::create([
                            'post_id' => $post->id,
                            'image_path' => $imagePath,
                        ]);
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        // Check extension & size for videos
                        if (!in_array($file->extension(), ['mp4', 'mov', 'avi', 'gif']) || $file->getSize() > 51200 * 1024) {
                            continue;
                        }

                        $videoPath = Helper::fileUpload($file, 'posts/videos', 'post-video-' . Str::random(8));
                        PostVideo::create([
                            'post_id' => $post->id,
                            'video_path' => $videoPath,
                        ]);
                    } else {
                        continue; // skip other files
                    }
                }
            }

            // Hashtag Extraction & Sync
            if ($request->filled('content')) {
                preg_match_all('/#(\w+)/', $request->input('content'), $matches);

                if (!empty($matches[1])) {
                    $tagIds = [];

                    foreach ($matches[1] as $tag) {
                        $hashtag = Hashtag::firstOrCreate(['tag' => '#' . $tag]);
                        $tagIds[] = $hashtag->id;
                    }

                    $post->hashtags()->sync($tagIds);
                }
            }

            DB::commit();

            return $this->success(new PostResource($post->load(['images', 'videos', 'hashtags'])), 'Post created successfully.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create post. ' . $e->getMessage(), 500);
        }
    }

    //get all posts of auth user
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Number of posts per page (default: 10)
            $perPage = $request->input('per_page', 10);

            // Get user's posts with relationships
            $posts = $user
                ->posts()
                ->with(['user', 'images', 'videos', 'hashtags', 'likes', 'comments.user'])
                ->latest()
                ->paginate($perPage);

            // Add is_liked flag
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                return $post;
            });

            return $this->success(
                new PostCollection($posts),
                'User posts retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch posts. ' . $e->getMessage(), 500);
        }
    }

    //get all posts of other users
    // public function getAllPosts(Request $request)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user) {
    //             return $this->error([], 'Unauthorized user.', 401);
    //         }

    //         // Number of posts per page (default: 10)
    //         $perPage = $request->input('per_page', 10);

    //         // Get all posts with relationships, paginated
    //         $posts = Post::with(['user', 'images', 'videos', 'hashtags', 'likes', 'comments.user'])
    //             ->latest()
    //             ->paginate($perPage);

    //         // Add is_liked for each post
    //         $posts->getCollection()->transform(function ($post) use ($user) {
    //             $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
    //             return $post;
    //         });

    //         // Use PostCollection
    //         return $this->success(
    //             new PostCollection($posts),
    //             'Newsfeed posts retrieved successfully.',
    //             200
    //         );
    //     } catch (Exception $e) {
    //         return $this->error([], 'Failed to fetch posts. ' . $e->getMessage(), 500);
    //     }
    // }

    public function getAllPosts(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Number of posts per page (default: 10)
            $perPage = $request->input('per_page', 10);

            // Get all posts with relationships, paginated
            $posts = Post::with(['user', 'images', 'videos', 'hashtags', 'likes', 'comments.user'])
                ->latest()
                ->paginate($perPage);

            // Add custom flags for each post
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();

                // New custom field
                $post->is_my_post = $post->user_id === $user->id;

                return $post;
            });

            // Use PostCollection
            return $this->success(
                new PostCollection($posts),
                'Newsfeed posts retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch posts. ' . $e->getMessage(), 500);
        }
    }


    //get single post
    public function show($id)
    {
        try {
            $post = Post::with(['user', 'images', 'videos', 'hashtags', 'comments'])->find($id);

            if (!$post) {
                return $this->error([], 'Post not found.', 404);
            }

            return $this->success(new PostResource($post), 'Post retrieved successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], 'Failed to retrieve post. ' . $e->getMessage(), 500);
        }
    }

    //delete post
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $post = Post::with(['images', 'videos', 'hashtags', 'likes', 'comments'])->find($id);

            if (!$post) {
                return $this->error([], 'Post not found.', 404);
            }

            // Check if post belongs to current user
            if ($post->user_id !== auth('api')->id()) {
                return $this->error([], 'Unauthorized to delete this post.', 403);
            }

            // Collect image paths and delete files
            $imagePaths = $post->images
                ->pluck('image_path')
                ->map(function ($path) {
                    return asset($path);
                })
                ->toArray();

            if (!empty($imagePaths)) {
                Helper::deleteImages($imagePaths);
            }

            // Delete video files manually (no helper yet)
            foreach ($post->videos as $video) {
                $fullPath = public_path($video->video_path);
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            // Delete related records from DB
            $post->images()->delete();
            $post->videos()->delete();
            $post->hashtags()->detach();

            // Finally delete the post
            $post->delete();

            DB::commit();

            return $this->success([], 'Post deleted successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to delete post. ' . $e->getMessage(), 500);
        }
    }

    //get posts by tag
    public function postsByTag(Request $request)
    {
        try {
            $tag = $request->query('tag');

            if (!$tag) {
                return $this->error([], 'Tag is required.', 422);
            }

            $hashtag = Hashtag::where('tag', '#' . $tag)->first();

            if (!$hashtag) {
                return $this->error([], 'Hashtag not found.', 404);
            }

            // Number of posts per page (default: 10)
            $perPage = $request->input('per_page', 10);

            // Fetch posts by tag with relationships
            $posts = $hashtag
                ->posts()
                ->with(['user', 'images', 'videos', 'hashtags', 'likes', 'comments.user'])
                ->latest()
                ->paginate($perPage);

            // Add is_liked flag for authenticated user
            if ($user = auth('api')->user()) {
                $posts->getCollection()->transform(function ($post) use ($user) {
                    $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                    return $post;
                });
            }

            return $this->success(
                new PostCollection($posts),
                'Posts fetched by tag: #' . $tag,
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch posts by tag. ' . $e->getMessage(), 500);
        }
    }

    //update post
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $post = Post::with(['images', 'videos', 'hashtags', 'likes', 'comments'])->find($id);

            if (!$post) {
                return $this->error([], 'Post not found.', 404);
            }

            if ($post->user_id !== auth('api')->id()) {
                return $this->error([], 'Unauthorized to update this post.', 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'content' => ['nullable', 'string'],
                'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'], // 5MB
                'videos.*' => ['nullable', 'mimes:mp4,mov,avi', 'max:51200'], // 50MB
            ]);

            if ($validator->fails()) {
                return $this->error(['Validation failed'], $validator->errors()->first(), 422);
            }

            // Update post content
            $post->update([
                'content' => $request->input('content'),
            ]);

            // Delete old images (optional, or let user remove selectively)
            $oldImages = $post->images->pluck('image_path')->map(fn($path) => asset($path))->toArray();
            Helper::deleteImages($oldImages);
            $post->images()->delete();

            // Upload new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'posts/images');
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }

            // Delete old videos
            foreach ($post->videos as $video) {
                $fullPath = public_path($video->video_path);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $post->videos()->delete();

            // Upload new videos
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'posts/videos', 'post-video-' . Str::random(8));
                    PostVideo::create([
                        'post_id' => $post->id,
                        'video_path' => $videoPath,
                    ]);
                }
            }

            // Sync hashtags
            if ($request->filled('content')) {
                preg_match_all('/#(\w+)/', $request->input('content'), $matches);

                if (!empty($matches[1])) {
                    $tagIds = [];
                    foreach ($matches[1] as $tag) {
                        $hashtag = Hashtag::firstOrCreate(['tag' => '#' . $tag]);
                        $tagIds[] = $hashtag->id;
                    }
                    $post->hashtags()->sync($tagIds);
                } else {
                    $post->hashtags()->detach(); // If no hashtags in updated content
                }
            }

            DB::commit();

            return $this->success(new PostResource($post->fresh(['images', 'videos', 'hashtags'])), 'Post updated successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to update post. ' . $e->getMessage(), 500);
        }
    }
}
