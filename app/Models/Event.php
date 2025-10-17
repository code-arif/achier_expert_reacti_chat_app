<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'user_id',
        'title',
        'description',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'time_zone',
        'all_day_event',
        'banner',
        'tags',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'all_day_event' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    // Relationships
    //venue model
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    //user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //likable
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }


    //comment
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    //tickets
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
