<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'title',
        'content',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // UUID settings
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (!$post->id) {
                $post->id = (string) Str::uuid();
            }
        });
    }
}