<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model
{

    protected $fillable = [
        'follower_id',
        'following_id',
    ];
}
