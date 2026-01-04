<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Support\Arr;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporters = User::where('role', 'user')->get();
        $posts = Post::all();
        $comments = Comment::all();

        if ($reporters->isEmpty() || ($posts->isEmpty() && $comments->isEmpty())) {
            $this->command->warn('Skipping ReportSeeder: Missing users, posts, or comments.');
            return;
        }

        $this->command->info('Seeding reports with UI-aligned reasons...');

        $reasonScenarios = [
            'Spam' => [
                'Repeated promotional links posted across multiple threads.',
                'Looks like automated bot activity.',
                'Advertising unrelated services.',
                'Suspicious external links.'
            ],
            'Hate Speech' => [
                'Uses offensive language targeting a group.',
                'Promotes hostility toward a protected group.',
                'Contains degrading stereotypes.'
            ],
            'Harassment' => [
                'Personal attacks against another user.',
                'Repeated aggressive replies.',
                'Threatening or intimidating language.'
            ],
            'Other' => [
                'Violates community guidelines.',
                'Inappropriate content for this platform.',
                'Disruptive behavior.',
                'Reported for moderator review.'
            ],
        ];

        for ($i = 0; $i < 20; $i++) {
            $reason = Arr::random(array_keys($reasonScenarios));

            Report::create([
                'reporter_id' => $reporters->random()->user_id,
                'reportable_id' => $posts->random()->post_id,
                'reportable_type' => Post::class,
                'reason' => $reason,
                'details' => Arr::random($reasonScenarios[$reason]),
                'status' => Arr::random(['pending', 'pending', 'reviewed']),
                'created_at' => now()->subDays(rand(0, 7)),
            ]);
        }

        for ($i = 0; $i < 15; $i++) {
            $reason = Arr::random(array_keys($reasonScenarios));

            Report::create([
                'reporter_id' => $reporters->random()->user_id,
                'reportable_id' => $comments->random()->comment_id,
                'reportable_type' => Comment::class,
                'reason' => $reason,
                'details' => Arr::random($reasonScenarios[$reason]),
                'status' => 'pending',
                'created_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('Reports seeded successfully using valid reasons.');
    }
}
