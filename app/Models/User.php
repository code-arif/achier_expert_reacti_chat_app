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


    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity_at' => 'datetime', 
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'reset_password_token_expire_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    // Friend requests
    public function sentRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    // Receive requests
    public function receivedRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }


    // User Model
    public function friends()
    {
        // where user_id is me
        $friends1 = $this->belongsToMany(
            User::class,
            'friends',
            'user_id',
            'friend_id'
        )->withTimestamps()->withPivot('became_friends_at');

        // Where friend_id is me
        $friends2 = $this->belongsToMany(
            User::class,
            'friends',
            'friend_id',
            'user_id'
        )->withTimestamps()->withPivot('became_friends_at');

        // When do Union then the same columns specify
        return $friends1->union($friends2->getQuery());
    }

    // message send
    public function sentMessages()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    // message received
    public function receivedMessages()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }


    // sender id
    public function senders()
    {
        return $this->belongsToMany(User::class, 'chats', 'receiver_id', 'sender_id')->distinct();
    }

    // receiver id
    public function receivers()
    {
        return $this->belongsToMany(User::class, 'chats', 'sender_id', 'receiver_id')->distinct();
    }

    // chat pertipent for group chat
    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    // is onlie check
    public function isOnline()
    {
        if (!$this->last_activity_at) {
            return false;
        }

        return $this->last_activity_at->diffInMinutes(now()) < 5;
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class, 'user_two_id');
    }

    public function allRooms()
    {
        return Room::where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
    }
}
