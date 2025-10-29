<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    protected $fillable = [
        'user_id',
        'block_user_id',
    ];

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'block_user_id');
    }
}
