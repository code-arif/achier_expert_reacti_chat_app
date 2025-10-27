<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    //fillable
    protected $fillable = [
        'user_id',
        'friend_id',
        'became_friends_at'
    ];

    public function friendUser()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
