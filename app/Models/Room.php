<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['user_one_id', 'user_two_id'];

    protected $casts = [
        'user_one_id' => 'integer',
        'user_two_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: First user in room
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Relationship: Second user in room
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }


    /**
     * Relationship: All messages in this room
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'room_id');
    }

    /**
     * Get the other user in the room (not the current user)
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->user_one_id == $currentUserId) {
            return $this->userTwo;
        }

        return $this->userOne;
    }

    /**
     * Check if a user is part of this room
     */
    public function hasUser($userId): bool
    {
        return $this->user_one_id == $userId || $this->user_two_id == $userId;
    }

    /**
     * Get last message in room
     */
    public function lastMessage()
    {
        return $this->hasOne(Chat::class, 'room_id')->latest();
    }

    /**
     * Get unread messages count for a specific user
     */
    public function unreadCountFor($userId): int
    {
        return $this->chats()
            ->where('receiver_id', $userId)
            ->where('status', '!=', 'read')
            ->count();
    }

    /**
     * Scope: Get rooms for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId);
    }

    /**
     * Scope: Get room between two users
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        $minId = min($userId1, $userId2);
        $maxId = max($userId1, $userId2);

        return $query->where('user_one_id', $minId)
            ->where('user_two_id', $maxId);
    }
}
