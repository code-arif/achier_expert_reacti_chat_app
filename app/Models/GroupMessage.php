<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'sender_id',
        'text',
        'file',
        'status',
        'is_blurred',
        'is_viewed',
        'message_type',
        'reply_to_message_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads()
    {
        return $this->hasMany(GroupMessageRead::class);
    }

    // Check if message is read by specific user
    public function isReadBy($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    // message status
    public function messageStatus()
    {
        return $this->hasMany(GroupMessageUserStatus::class, 'message_id');
    }

    /**
     * The message this message is replying to
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'reply_to_message_id');
    }

    /**
     * All replies to this message
     */
    public function replies(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'reply_to_message_id');
    }

    // public function currentUserStatus()
    // {
    //     return $this->hasOne(GroupMessageUserStatus::class, 'message_id');
    // }
}
