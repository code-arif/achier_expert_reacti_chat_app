<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageDeletion extends Model
{
    protected $fillable = [
        'chat_id',
        'deleted_by',
        'deletion_type',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
