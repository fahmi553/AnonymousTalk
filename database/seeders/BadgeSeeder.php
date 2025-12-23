<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        $badges = [

            [
                'badge_name' => 'Newcomer',
                'badge_type' => 'achievement',
                'trust_threshold' => 0,
                'description' => 'Joined the community',
                'icon_path' => 'badges/newcomer.png',
            ],
            [
                'badge_name' => 'Regular',
                'badge_type' => 'achievement',
                'trust_threshold' => 10,
                'description' => 'Maintains a positive trust score',
                'icon_path' => 'badges/regular.png',
            ],
            [
                'badge_name' => 'Trusted Voice',
                'badge_type' => 'achievement',
                'trust_threshold' => 30,
                'description' => 'Frequently contributes constructively',
                'icon_path' => 'badges/trusted_voice.png',
            ],
            [
                'badge_name' => 'Community Pillar',
                'badge_type' => 'achievement',
                'trust_threshold' => 50,
                'description' => 'Highly trusted by the community',
                'icon_path' => 'badges/community_pillar.png',
            ],
            [
                'badge_name' => 'Verified Member',
                'badge_type' => 'achievement',
                'trust_threshold' => 70,
                'description' => 'Consistently positive behavior over time',
                'icon_path' => 'badges/verified_member.png',
            ],
            [
                'badge_name' => 'Trusted Contributor',
                'badge_type' => 'achievement',
                'trust_threshold' => 80,
                'description' => 'Exceptional contributions and reliability',
                'icon_path' => 'badges/trusted_contributor.png',
            ],
            [
                'badge_name' => 'Community Guardian',
                'badge_type' => 'achievement',
                'trust_threshold' => 90,
                'description' => 'Top-tier trusted member',
                'icon_path' => 'badges/community_guardian.png',
            ],

            [
                'badge_name' => 'Consistent Poster',
                'badge_type' => 'achievement',
                'trust_threshold' => null,
                'description' => 'Posted regularly without moderation',
                'icon_path' => 'badges/consistent_poster.png',
            ],
            [
                'badge_name' => 'Helpful Commenter',
                'badge_type' => 'achievement',
                'trust_threshold' => null,
                'description' => 'Frequently leaves helpful comments',
                'icon_path' => 'badges/helpful_commenter.png',
            ],
            [
                'badge_name' => 'Civil Contributor',
                'badge_type' => 'achievement',
                'trust_threshold' => null,
                'description' => 'No moderated posts or comments',
                'icon_path' => 'badges/civil_contributor.png',
            ],
            [
                'badge_name' => 'Respectful Debater',
                'badge_type' => 'achievement',
                'trust_threshold' => null,
                'description' => 'Engages respectfully in discussions',
                'icon_path' => 'badges/respectful_debater.png',
            ],
            [
                'badge_name' => 'Listener',
                'badge_type' => 'achievement',
                'trust_threshold' => null,
                'description' => 'Engages more than they post',
                'icon_path' => 'badges/listener.png',
            ],

            [
                'badge_name' => 'Under Review',
                'badge_type' => 'status',
                'trust_threshold' => null,
                'description' => 'Currently under moderation review',
                'icon_path' => 'badges/under_review.png',
            ],
            [
                'badge_name' => 'On Probation',
                'badge_type' => 'status',
                'trust_threshold' => null,
                'description' => 'Low trust score due to violations',
                'icon_path' => 'badges/on_probation.png',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['badge_name' => $badge['badge_name']],
                $badge
            );
        }
    }
}
