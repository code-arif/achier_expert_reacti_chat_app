<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_id',
        'message',
        'type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'thumbnail_path',
        'audio_duration',
        'video_duration',
        'link_preview',
        'status',
        'delivered_at',
        'read_at',
        'reply_to_id',
        'forwarded_from_id',
        'reactions',
    ];

    protected $casts = [
        'link_preview' => 'array',
        'reactions' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected $appends = ['short_text', 'humanize_date'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(Chat::class, 'reply_to_id');
    }

    public function forwardedFrom()
    {
        return $this->belongsTo(Chat::class, 'forwarded_from_id');
    }

    public function deletions()
    {
        return $this->hasMany(MessageDeletion::class);
    }

    public function getShortTextAttribute()
    {
        if ($this->type === 'text') {
            return Str::limit($this->message, 50);
        }

        $typeLabels = [
            'image' => '📷 Photo',
            'video' => '🎥 Video',
            'audio' => '🎵 Audio',
            'document' => '📄 Document',
            'link' => '🔗 Link',
        ];

        return $typeLabels[$this->type] ?? 'Message';
    }

    public function getHumanizeDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function isDeletedFor($userId)
    {
        return $this->deletions()->where('deleted_by', $userId)->exists();
    }

    public function scopeNotDeletedBy($query, $userId)
    {
        return $query->whereNotIn('id', function ($subQuery) use ($userId) {
            $subQuery->select('chat_id')
                ->from('message_deletions')
                ->where('deleted_by', $userId);
        });
    }
}
