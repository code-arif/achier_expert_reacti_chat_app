<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['commentable', 'user_id', 'comment', 'parent_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user'); // eager load user of reply
    }


    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // commantable
    public function commentable()
    {
        return $this->morphTo();
    }
}
