<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessageUserStatus extends Model
{
    protected $fillable = [
        'message_id',
        'user_id',
        'is_viewed',
        'is_blurred',
    ];

    // Relationships
    public function message()
    {
        return $this->belongsTo(GroupMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
