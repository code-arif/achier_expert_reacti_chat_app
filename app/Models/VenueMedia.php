<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VenueMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'image_url',
        'video_url',
    ];

    
    // hidden data
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relationship
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
