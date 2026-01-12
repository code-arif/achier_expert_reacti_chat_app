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

    public function getAvatarAttribute($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }
        return $value;
    }

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

    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id')
            ->withTimestamps();
    }

    // Optional: Combined friends (both directions)
    public function allFriends()
    {
        return $this->friends()->orWhere(function ($query) {
            $query->whereIn('friend_id', $this->friendOf()->pluck('user_id'));
        });
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

    // New relationships for group chat
    public function groupMemberships()
    {
        return $this->hasMany(GroupMember::class, 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    public function groupMessages()
    {
        return $this->hasMany(GroupMessage::class, 'sender_id');
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseTokens::class);
    }
}
