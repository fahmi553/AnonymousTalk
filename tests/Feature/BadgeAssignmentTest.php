<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BadgeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\BadgeSeeder::class);
    }

    public function user_gets_correct_trust_badge()
    {
        $user = User::factory()->create([
            'trust_score' => 0,
        ]);

        $user->applyTrustChange(35, 'test');

        $badge = $user->badges()->first();

        $this->assertNotNull($badge);
        $this->assertEquals('Trusted Voice', $badge->badge_name);
    }

    public function trust_badges_are_exclusive()
    {
        $user = User::factory()->create(['trust_score' => 0]);

        $user->applyTrustChange(95, 'test');

        $trustBadges = $user->badges()
            ->where('trust_threshold', '>', 0)
            ->get();

        $this->assertCount(1, $trustBadges);
        $this->assertEquals('Community Guardian', $trustBadges->first()->badge_name);
    }

    public function behavior_badges_can_stack()
    {
        $user = User::factory()->create(['trust_score' => 50]);

        $user->posts()->createMany(array_fill(0, 10, [
            'title' => 'Test',
            'content' => 'Test',
            'status' => 'published',
        ]));

        $user->comments()->createMany(array_fill(0, 20, [
            'content' => 'Test',
            'status' => 'published',
        ]));

        $user->updateBadges();

        $badgeNames = $user->badges()->pluck('badge_name')->toArray();

        $this->assertContains('Consistent Poster', $badgeNames);
        $this->assertContains('Helpful Commenter', $badgeNames);
    }
}
