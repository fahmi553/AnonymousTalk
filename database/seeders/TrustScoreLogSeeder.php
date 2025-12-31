<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TrustScoreLog;
use Illuminate\Support\Arr;

class TrustScoreLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed Users first.');
            return;
        }

        $this->command->info('Seeding Trust Score Logs...');

        $actions = [
            'post_flagged' => [
                'score' => -5, 
                'reasons' => ['Contains hate speech.', 'Harassment detected.', 'Spam link posted.']
            ],
            'post_approved' => [
                'score' => 0, 
                'reasons' => ['False positive flag cleared by admin.', 'Post reviewed and safe.']
            ],
            'warn' => [
                'score' => -10, 
                'reasons' => ['Admin issued a manual warning.', 'Too many reports received.']
            ],
            'reward' => [
                'score' => 5, 
                'reasons' => ['Consistent positive behavior.', 'Helpful community report.']
            ],
            'ban' => [
                'score' => 0, 
                'reasons' => ['Violation of Terms of Service.', 'Repeated severe toxicity.']
            ]
        ];
        for ($i = 0; $i < 30; $i++) {
            $actionType = array_rand($actions);
            $data = $actions[$actionType];
            $randomDate = now()->subDays(rand(0, 30))->subHours(rand(0, 24));

            TrustScoreLog::create([
                'user_id' => $users->random()->user_id,
                'action_type' => $actionType,
                'score_change' => $data['score'],
                'reason' => Arr::random($data['reasons']),
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }

        $this->command->info('Trust Score Logs seeded successfully!');
    }
}