<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportedUser extends Model
{
    protected $fillable = ['user_id', 'reported_user_id', 'reason', 'description'];

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id', 'id')
            ->select('id', 'first_name', 'last_name', 'username', 'avatar');
    }
}
