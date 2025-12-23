<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Badge;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'Admin',
                'password' => bcrypt('Qawsedrftgyh456@'),
                'trust_score' => 100,
                'role' => 'admin'
            ]
        );

        $adminBadge = Badge::where('badge_name', 'Community Guardian')->first();
        if ($adminBadge) {
            $admin->badges()->syncWithoutDetaching([$adminBadge->badge_id => ['awarded_at' => now()]]);
        }

        // Regular users
        $users = [
            ['ali@gmail.com', 'BoldCat596', 80],
            ['sara@gmail.com', 'GracefulFalcon27', 72],
            ['john@gmail.com', 'CuriousOtter102', 65],
            ['lisa@gmail.com', 'BrightFox88', 55],
            ['mike@gmail.com', 'IntrepidGiraffe90', 40],
        ];

        foreach ($users as [$email, $username, $score]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'password' => bcrypt('Qawsedrftgyh456@'),
                    'trust_score' => $score,
                    'role' => 'user'
                ]
            );

            $trustBadge = Badge::whereNotNull('trust_threshold')
                ->where('trust_threshold', '<=', $score)
                ->orderByDesc('trust_threshold')
                ->first();

            if ($trustBadge) {
                $user->badges()->syncWithoutDetaching([$trustBadge->badge_id => ['awarded_at' => now()]]);
            }

            $user->updateBadges();
        }

        $this->command->info('Users seeded successfully with realistic trust scores and badges.');
    }
}
