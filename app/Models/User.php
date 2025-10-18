<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'trust_score',
        'role',
        'badge_id',
        'hide_all_posts',
        'hide_all_comments',
    ];

    protected $casts = [
        'hide_all_posts' => 'boolean',
        'hide_all_comments' => 'boolean',
    ];

    protected $hidden = ['password'];

    const TRUST_SCORE_POST_REWARD = 2;
    const TRUST_SCORE_COMMENT_REWARD = 1;
    const TRUST_SCORE_POST_PENALTY = -2;
    const TRUST_SCORE_COMMENT_PENALTY = -1;

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function trustScoreLogs()
    {
        return $this->hasMany(TrustScoreLog::class, 'user_id');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id')
            ->withTimestamps()
            ->withPivot('awarded_at');
    }

    public function currentBadge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function updateTrustScore($amount)
    {
        $this->trust_score = max(0, $this->trust_score + $amount);
        $this->save();
    }
}
