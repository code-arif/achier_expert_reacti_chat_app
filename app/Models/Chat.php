<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'text',
        'file',
        'room_id'
    ];

    protected $hidden = [
        'create_at',
        'updated_at',
        'deleted_at'
    ];

    protected function casts(): array {
        return [
            'sender_id'   => 'integer' ,
            'receiver_id' => 'integer',
            'text'        => 'string'
        ];
    }

    protected $appends = [
        'humanize_date',
        'short_text',
        'type'
    ];


    public function getFileAttribute($value): ?string
    {
        if(filter_var($value , FILTER_VALIDATE_URL)){
            return $value;
        }

        return $value ? url($value) : null;
    }

    public function getShortTextAttribute(): string | null
    {
        return strlen($this->text) > 20 ? substr($this->text , 0 , 20) . '...' : $this->text;
    }

    public function getHumanizeDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getTypeAttribute(): string
    {
        if(request()->is('api/*')){
            return $this->sender_id = auth('api')->id() ? 'sent' : 'received';
        }

        return $this->sender_id == auth('web')->user()->id ? 'sent' : 'received';
    }

    public function sender(): BelongsTo {
        return $this->belongsTo(User::class , 'sender_id');
    }

    public function receiver(): BelongsTo {
        return $this->belongsTo(User::class , 'receiver_id');
    }

    public function room(): BelongsTo {
        return $this->belongsTo(Room::class );
    }

}
