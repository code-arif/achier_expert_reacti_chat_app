<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'like_count',
        'comment_count',
        'share_count'
    ];


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    public function videos()
    {
        return $this->hasMany(PostVideo::class);
    }

    // like
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    //comment
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }


    // post hashtag
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_posts');
    }

    //posts
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'hashtag_posts')
            ->withTimestamps();
    }
}
