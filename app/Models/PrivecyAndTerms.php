<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivecyAndTerms extends Model
{
    protected $table = 'privecy_and_terms';

    protected $fillable = ['type', 'description'];
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Get the type of the privacy and terms.
     */
    public function getTypeAttribute($value)
    {
        return ucfirst($value);
    }
}
