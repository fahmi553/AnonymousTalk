<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'sentiment_score',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reported_post_id');
    }

    public function sentimentLogs()
    {
        return $this->hasMany(PostSentimentLog::class, 'post_id');
    }
}
