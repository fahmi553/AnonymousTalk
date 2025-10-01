<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'sentiment_score',
        'parent_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sentimentLogs()
    {
        return $this->hasMany(CommentSentimentLog::class, 'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with(['user', 'replies']);
    }

    public function parentComment()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}
