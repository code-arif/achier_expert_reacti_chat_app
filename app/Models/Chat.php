<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'text',
        'file',
        'file_type',
        'thumbnail',
        'room_id',
        'status',
        'is_blurred',
        'is_viewed',
        'message_type',
        'reply_to_id',
        'forwarded_from',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected function casts(): array
    {
        return [
            'sender_id' => 'integer',
            'receiver_id' => 'integer',
            'room_id' => 'integer',
            'text' => 'string',
            'is_blurred' => 'boolean',
            'is_viewed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected $appends = [
        'humanize_date',
        'short_text',
        'type',
        'media_type',
    ];


    /**
     * Get file URL (already stored as full S3 URL)
     */
    public function getFileAttribute($value): ?string
    {
        return $value;
    }


    /**
     * Get short version of text for list views
     */
    public function getShortTextAttribute(): ?string
    {
        if (!$this->text) {
            return null;
        }

        return mb_strlen($this->text, 'UTF-8') > 50
            ? mb_substr($this->text, 0, 50, 'UTF-8') . '...'
            : $this->text;
    }


    /**
     * Get human-readable date
     */
    public function getHumanizeDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }


    /**
     * Determine message type (sent/received) based on auth user
     */
    public function getTypeAttribute(): string
    {
        $currentUserId = null;

        if (request()->is('api/*')) {
            $currentUserId = auth('api')->id();
        } else {
            $currentUserId = auth('web')->id();
        }

        return $this->sender_id == $currentUserId ? 'sent' : 'received';
    }

    /**
     * Get media type from file
     */
    public function getMediaTypeAttribute(): ?string
    {
        if (!$this->file) {
            return null;
        }

        // If file_type is already stored, use it
        if ($this->attributes['file_type'] ?? null) {
            return $this->attributes['file_type'];
        }

        // Otherwise detect from extension
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'heic', 'heif'];
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        }

        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', '3gp', 'mpeg'];
        if (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        if (in_array($extension, $audioExtensions)) {
            return 'audio';
        }

        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        if (in_array($extension, $documentExtensions)) {
            return 'document';
        }

        return 'file';
    }

    /**
     * Relationship: Message sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship: Message receiver
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Relationship: Chat room
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Relationship: Reply-to message
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'reply_to_id');
    }

    public function parentReply()
    {
        return $this->belongsTo(Chat::class, 'reply_to_id');
    }

    /**
     * Relationship: Forwarded from user
     */
    public function forwardedFromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_from');
    }

    /**
     * Scope: Get messages for a specific room
     */
    public function scopeForRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope: Get unread messages for a user
     */
    public function scopeUnreadFor($query, $userId)
    {
        return $query->where('receiver_id', $userId)
            ->where('status', '!=', 'read');
    }

    /**
     * Scope: Get messages between two users
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['status' => 'read']);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->update(['status' => 'delivered']);
    }

    /**
     * Check if message is from auth user
     */
    public function isMine(): bool
    {
        $currentUserId = request()->is('api/*')
            ? auth('api')->id()
            : auth('web')->id();

        return $this->sender_id == $currentUserId;
    }

    /**
     * Check if message has media
     */
    public function hasMedia(): bool
    {
        return !is_null($this->file);
    }

    /**
     * Check if message is a reply
     */
    public function isReply(): bool
    {
        return !is_null($this->reply_to_id);
    }

    /**
     * Check if message is forwarded
     */
    public function isForwarded(): bool
    {
        return !is_null($this->forwarded_from);
    }
}
