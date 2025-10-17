<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'title',
        'description',
        'all_day',
        'start_date',
        'end_date',
        'color_code',
    ];

    // Relationships

    /**
     * Get the user that owns the calendar event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated event, if any.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
