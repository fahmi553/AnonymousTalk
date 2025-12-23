<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Badge;

class BadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_trust_badge_when_threshold_met()
    {
        $user = User::factory()->create(['trust_score' => 40]);

        $badge = Badge::factory()->create([
            'badge_name' => 'Trusted Voice',
            'trust_threshold' => 30,
        ]);

        $user->badges()->attach($badge);

        $this->assertTrue(
            $user->badges()->pluck('badge_id')->contains($badge->badge_id)
        );
    }
}
