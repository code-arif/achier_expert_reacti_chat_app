<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'status',
        'btn_link',
        'btn_color',
        'metadata',
        'bg'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'blog_id');
    }

    public function getImageAttribute($value): string | null
    {
        if (filter_var($value)) {
            return $value;
        }

        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }
        return $value;
    }

    public function getBgAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }
        return $value;
    }
}
