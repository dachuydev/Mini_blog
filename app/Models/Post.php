<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($post) {
            Cache::flush();
        });

        static::updated(function ($post) {
            Cache::flush();
            Cache::forget('post_' . $post->id);
        });

        static::deleted(function ($post) {
            Cache::flush();
            Cache::forget('post_' . $post->id);
        });
    }
} 