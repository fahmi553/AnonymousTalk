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
        'hidden_in_profile',
        'status',
    ];

    protected $casts = [
        'hidden_in_profile' => 'boolean',
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
            ->where('status', 'published')
            ->with(['user', 'replies', 'parentComment.user']);
    }

    public function parentComment()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('status', 'published');
    }

    public function reports()
    {
        return $this->morphMany(\App\Models\Report::class, 'reportable');
    }
}
