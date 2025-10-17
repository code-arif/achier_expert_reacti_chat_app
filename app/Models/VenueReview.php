<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueReview extends Model
{
    protected $fillable = [
        'venue_id',
        'user_id',
        'comment',
        'rating'
    ];

    //relation with user table
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
