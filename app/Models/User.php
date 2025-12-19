<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Badge;
use App\Models\TrustScoreLog;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function updateTrustScore($amount, $reason = 'Activity Reward/Penalty')
    {
        $this->trust_score = max(0, $this->trust_score + $amount);
        $this->save();

        TrustScoreLog::create([
            'user_id' => $this->user_id,
            'action_type' => $amount >= 0 ? 'reward' : 'penalty',
            'score_change' => $amount,
            'reason' => $reason,
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->evaluateRestrictions();
        $this->checkForBadges();
    }

    public function checkForBadges()
    {
        $eligibleBadges = Badge::where(
            'trust_threshold', '<=', $this->trust_score
        )->orderBy('trust_threshold')->get();

        foreach ($eligibleBadges as $badge) {
            if (!$this->badges->contains($badge->badge_id)) {
                $this->badges()->attach($badge->badge_id, [
                    'awarded_at' => now(),
                ]);

                $this->badge_id = $badge->badge_id;
                $this->save();
            }
        }
    }

    public function evaluateRestrictions()
    {
        if ($this->trust_score < 5) {
            $this->hide_all_comments = true;
        } else {
            $this->hide_all_comments = false;
        }

        if ($this->trust_score < 0) {
            $this->hide_all_posts = true;
        } else {
            $this->hide_all_posts = false;
        }

        $this->save();
    }
}