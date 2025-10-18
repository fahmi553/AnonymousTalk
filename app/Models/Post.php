<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public const STATUSES = ['published', 'moderated', 'deleted'];

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category_id',
        'sentiment_score',
        'status',
        'hidden_in_profile',
    ];

    protected $casts = [
        'hidden_in_profile' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id')->latest();
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reported_post_id');
    }

    public function sentimentLogs()
    {
        return $this->hasMany(PostSentimentLog::class, 'post_id');
    }

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    public function getRouteKeyName()
    {
        return 'post_id';
    }
}
