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
        'hide_all_posts',
        'hide_all_comments',
        'can_post',
        'can_comment',
    ];

    protected $casts = [
        'hide_all_posts' => 'boolean',
        'hide_all_comments' => 'boolean',
        'can_post' => 'boolean',
        'can_comment' => 'boolean',
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

    public function applyTrustChange(int $change, string $reason, string $actionType = 'system')
    {
        if ($change === 0) {
            return;
        }

        $this->trust_score = max(0, min(100, $this->trust_score + $change));
        $this->save();

        TrustScoreLog::create([
            'user_id' => $this->user_id,
            'action_type' => $actionType,
            'score_change' => $change,
            'reason' => $reason,
            'timestamp' => now(),
        ]);

        $this->enforceTrustConsequences();
        $this->updateBadges();
    }

    public function enforceTrustConsequences()
    {
        if ($this->trust_score < 10) {
            $this->update([
                'can_post' => false,
                'can_comment' => false,
            ]);
        } elseif ($this->trust_score < 30) {
            $this->update([
                'can_post' => false,
                'can_comment' => true,
            ]);
        } else {
            $this->update([
                'can_post' => true,
                'can_comment' => true,
            ]);
        }

        $this->updateRestrictions();
    }

    private function updateRestrictions()
    {
        $this->hide_all_comments = $this->trust_score < 5;
        $this->hide_all_posts = false;
        $this->save();
    }

    public function updateBadges()
    {
        $allBadges = Badge::all();
        $userBadgeIds = $this->badges()->pluck('user_badges.badge_id')->toArray();

        $trustBadges = $allBadges->whereNotNull('trust_threshold');
        $behaviorBadges = $allBadges->whereNull('trust_threshold');

        $this->updateTrustBadges($trustBadges);
        $this->updateBehaviorBadges($behaviorBadges, $userBadgeIds);
    }

    private function updateTrustBadges($trustBadges)
    {
        $eligible = $trustBadges
            ->where('trust_threshold', '<=', $this->trust_score)
            ->sortByDesc('trust_threshold')
            ->first();

        $this->badges()->detach($trustBadges->pluck('badge_id')->toArray());

        if ($eligible) {
            $this->badges()->attach($eligible->badge_id, [
                'awarded_at' => now(),
            ]);
        }
    }
    private function updateBehaviorBadges($behaviorBadges, $userBadgeIds)
    {
        $publishedPosts = $this->posts()->where('status', 'published')->count();
        $publishedComments = $this->comments()->where('status', 'published')->count();
        $moderatedCount =
            $this->posts()->where('status', 'moderated')->count() +
            $this->comments()->where('status', 'moderated')->count();
        $safeReplies = $this->comments()
            ->whereNotNull('parent_id')
            ->where('status', 'published')
            ->count();

        $conditions = [
            'Consistent Poster'  => $publishedPosts >= 10,
            'Helpful Commenter'  => $publishedComments >= 20,
            'Civil Contributor'  => $moderatedCount === 0,
            'Respectful Debater' => $safeReplies >= 10,
            'Listener'           => $publishedPosts > 0 && $publishedComments >= ($publishedPosts * 3),
            'Under Review'       => $moderatedCount >= 3,
            'On Probation'       => $this->trust_score < 10,
        ];

        $attach = [];
        $detach = [];

        foreach ($behaviorBadges as $badge) {
            $hasBadge = in_array($badge->badge_id, $userBadgeIds);
            $conditionMet = $conditions[$badge->badge_name] ?? false;

            if ($conditionMet && !$hasBadge) {
                $attach[$badge->badge_id] = ['awarded_at' => now()];
            }

            if (!$conditionMet && $hasBadge) {
                $detach[] = $badge->badge_id;
            }
        }

        if (!empty($attach)) {
            $this->badges()->attach($attach);
        }

        if (!empty($detach)) {
            $this->badges()->detach($detach);
        }
    }
}
