<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['likeable_id', 'user_id', 'likeable_type'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //likable 
    public function likeable()
    {
        return $this->morphTo();
    }
}
