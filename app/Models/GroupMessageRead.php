<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMessageRead extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group_message_id',
        'user_id',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
