<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $fillable = ['tag'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'hashtag_posts');
    }
}
