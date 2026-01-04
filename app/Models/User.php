<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Badge;
use App\Models\TrustScoreLog;
use Illuminate\Support\Facades\Log;
use App\Notifications\BadgeEarnedNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'trust_score',
        'role',
        'hide_all_posts',
        'hide_all_comments',
        'can_post',
        'can_comment',
        'avatar',
    ];

    protected $appends = ['avatar_url'];

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

    public function reportsFiled()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
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

        $oldScore = $this->trust_score;

        $this->trust_score = max(0, min(100, $this->trust_score + $change));
        $this->save();

        Log::info('Trust score updated', [
            'user_id' => $this->user_id,
            'old_score' => $oldScore,
            'change' => $change,
            'new_score' => $this->trust_score,
            'reason' => $reason,
            'action_type' => $actionType,
        ]);

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
        }
        else {
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
        $previousTrustBadges = $this->badges()->where('trust_threshold', '>', 0)->get();
        $previousMaxThreshold = $previousTrustBadges->max('trust_threshold') ?? 0;

        $beforeBadgeIds = $this->badges()->pluck('user_badges.badge_id')->toArray();

        $allBadges = \App\Models\Badge::all();
        $trustBadges = $allBadges->where('trust_threshold', '>', 0);
        $behaviorBadges = $allBadges->where('trust_threshold', 0);

        $negativeBadgeNames = ['Toxic', 'Under Review', 'Warned', 'Banned'];

        $this->updateTrustBadges($trustBadges);
        $this->updateBehaviorBadges($behaviorBadges, $beforeBadgeIds);

        $this->load('badges');
        $afterBadges = $this->badges;

        foreach ($afterBadges as $badge) {
            if (!in_array($badge->badge_id, $beforeBadgeIds)) {

                $notificationType = 'achievement';

                if (in_array($badge->badge_name, $negativeBadgeNames) || str_contains(strtolower($badge->badge_name), 'toxic')) {
                    $notificationType = 'negative';
                }
                elseif ($badge->trust_threshold > 0) {
                    if ($badge->trust_threshold > $previousMaxThreshold) {
                        $notificationType = 'promotion';
                    } elseif ($badge->trust_threshold < $previousMaxThreshold) {
                        $notificationType = 'demotion';
                    }
                }

                $this->notify(new \App\Notifications\BadgeEarnedNotification($badge->badge_name, $notificationType));
            }
        }
    }

    private function updateTrustBadges($trustBadges)
    {
        Log::info('Evaluating trust badges', [
            'user_id' => $this->user_id,
            'trust_score' => $this->trust_score,
        ]);

        $trustBadgeIds = $trustBadges->pluck('badge_id')->toArray();

        $this->badges()->detach($trustBadgeIds);

        $eligible = $trustBadges
            ->where('trust_threshold', '<=', $this->trust_score)
            ->sortByDesc('trust_threshold')
            ->first();

        if ($eligible) {
            $this->badges()->attach($eligible->badge_id, [
                'awarded_at' => now(),
            ]);

            Log::info('Assigned trust badge', [
                'user_id' => $this->user_id,
                'badge' => $eligible->badge_name,
                'threshold' => $eligible->trust_threshold,
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

        foreach ($behaviorBadges as $badge) {
            $hasBadge = in_array($badge->badge_id, $userBadgeIds);
            $conditionMet = $conditions[$badge->badge_name] ?? false;

            if ($conditionMet && !$hasBadge) {
                $this->badges()->attach($badge->badge_id, [
                    'awarded_at' => now(),
                ]);

                Log::info('Behavior badge attached', [
                    'user_id' => $this->user_id,
                    'badge' => $badge->badge_name,
                ]);
            }

            if (!$conditionMet && $hasBadge) {
                $this->badges()->detach($badge->badge_id);

                Log::info('Behavior badge detached', [
                    'user_id' => $this->user_id,
                    'badge' => $badge->badge_name,
                ]);
            }
        }
    }

    public function getAvatarUrlAttribute()
    {
        $avatar = $this->avatar ?? 'default.jpg';

        if (str_starts_with($avatar, 'http')) {
            return $avatar;
        }
        return asset("images/avatars/{$avatar}");
    }

}
