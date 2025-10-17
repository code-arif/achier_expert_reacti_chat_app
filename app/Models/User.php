<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, Billable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'mobile_number',
        'email',
        'phone',
        'password',
        'avatar',
        'cover',
        'bio',
        'address',
        'otp',
        'otp_expires_at',
        'otp_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'last_activity_at',
        'status',
        'is_google_signin',
        'google_id',
        'is_apple_signin',
        'apple_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'otp',
        'reset_password_token',
    ];



    //name getter
    public function getFirstNameAttribute($value): string
    {
        return ucfirst($value ?? '');
    }


    //gender getter
    public function getGenderAttribute($value)
    {
        return ucfirst($value);
    }

    public function senders()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function roomsAsUserOne()
    {
        return $this->hasMany(Room::class, 'user_one_id');
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class, 'user_two_id');
    }

    public function allRooms()
    {
        return Room::where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
    }


    //posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    //post images
    // User.php
    public function postImages()
    {
        return $this->hasManyThrough(
            PostImage::class, // Final model
            Post::class,      // Intermediate model
            'user_id',        // Foreign key on posts table
            'post_id',        // Foreign key on post_images table
            'id',             // Local key on users table
            'id'              // Local key on posts table
        );
    }


    //likes
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    //comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //follower
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'following_id', 'follower_id');
    }

    //following
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'follower_id', 'following_id');
    }


    //venuse table relation
    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    //events table relation
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
