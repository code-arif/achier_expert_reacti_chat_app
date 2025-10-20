<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockedUser extends Model
{
     use HasFactory;

    protected $fillable = [
        'user_id',
        'blocked_user_id',
        'reason',
        'description',
    ];

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
