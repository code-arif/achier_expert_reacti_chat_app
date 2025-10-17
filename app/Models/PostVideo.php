<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVideo extends Model
{
    protected $fillable = ['post_id', 'video_path'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
