<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TrustScoreLog;
use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;

class TrustScoreLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $reports = Report::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed Users first.');
            return;
        }

        if ($reports->isEmpty()) {
            $this->command->warn('No reports found. Seed reports first.');
            return;
        }

        $this->command->info('Seeding realistic Trust Score Logs based on reports...');

        foreach ($reports as $report) {
            $targetUser = null;

            if ($report->reportable_type === Post::class || $report->reportable_type === Comment::class) {
                $targetUser = $report->reportable->user ?? null;
            } elseif ($report->reportable_type === User::class) {
                $targetUser = $report->reportable;
            }

            if (!$targetUser) continue;

            $scoreChange = rand(5, 10);
            $actionType = rand(0, 1) ? 'post_flagged' : 'warn';

            TrustScoreLog::create([
                'user_id' => $targetUser->user_id,
                'action_type' => $actionType,
                'score_change' => -$scoreChange,
                'reason' => "Reported for: {$report->reason}. {$report->details}",
                'created_at' => $report->created_at->addMinutes(rand(1, 120)),
                'updated_at' => $report->created_at->addMinutes(rand(1, 120)),
            ]);

            $targetUser->trust_score = max(0, $targetUser->trust_score - $scoreChange);
            $targetUser->save();
        }

        foreach ($users as $user) {
            $userReports = $reports->filter(function ($r) use ($user) {
                if ($r->reportable_type === Post::class || $r->reportable_type === Comment::class) {
                    return $r->reportable->user_id === $user->user_id;
                } elseif ($r->reportable_type === User::class) {
                    return $r->reportable->user_id === $user->user_id;
                }
                return false;
            });

            if ($userReports->isEmpty()) {
                $scoreChange = 5;
                TrustScoreLog::create([
                    'user_id' => $user->user_id,
                    'action_type' => 'reward',
                    'score_change' => $scoreChange,
                    'reason' => 'Consistently positive contributions to the community.',
                    'created_at' => now()->subDays(rand(1, 30))->subHours(rand(0, 23)),
                    'updated_at' => now()->subDays(rand(1, 30))->subHours(rand(0, 23)),
                ]);

                $user->trust_score += $scoreChange;
                $user->save();
            }
        }

        $this->command->info('Trust Score Logs seeded successfully with realistic context!');
    }
}
