<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['user_one_id' , 'user_two_id'];

    public function userOne()
    {
        return $this->belongsTo(User::class , 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class , 'user_two_id');
    }


    public function chats()
    {
        return $this->belongsTo(Chat::class);
    }
}
