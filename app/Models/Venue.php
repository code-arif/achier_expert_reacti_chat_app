<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'capacity',
        'location',
        'latitude',
        'longitude',
        'service_start_time',
        'service_end_time',
        'ticket_price',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'service_start_time' => 'datetime:H:i:s',
        'service_end_time' => 'datetime:H:i:s',
        'latitude'  => 'float',
        'longitude' => 'float',
        'ticket_price' => 'float',
    ];


    // Relationships
    //venue-details table
    public function detail()
    {
        return $this->hasOne(VenueDetail::class);
    }

    // veune media table
    public function media()
    {
        return $this->hasMany(VenueMedia::class);
    }

    //venue review table
    public function reviews()
    {
        return $this->hasMany(VenueReview::class);
    }
}
