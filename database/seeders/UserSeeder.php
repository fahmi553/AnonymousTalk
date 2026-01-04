<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Support\Str;
use App\Helpers\UsernameGenerator;

class UserSeeder extends Seeder
{
    public function run()
    {
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
            $admin->badges()->syncWithoutDetaching([
                $adminBadge->badge_id => ['awarded_at' => now()]
            ]);
        }
        $existingUsers = [
            ['foxiestfox07@gmail.com', 'BoldCat596', 80],
            ['fahmikhalidqawsedrftgyh456@gmail.com', 'GracefulFalcon27', 72],
            ['ali@gmail.com', 'CuriousOtter102', 65],
            ['lisa@gmail.com', 'BrightFox88', 55],
            ['mike@gmail.com', 'IntrepidGiraffe90', 40],
        ];

        foreach ($existingUsers as [$email, $username, $score]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'password' => bcrypt('Qawsedrftgyh456@'),
                    'trust_score' => $score,
                    'role' => 'user'
                ]
            );

            $user->updateBadges();
        }

        $newUsersScores = [
            85, 82, 78, 76, 75,
            68, 65, 62, 58, 55,
            52, 48, 45, 42, 40,
            35, 32, 30, 28, 25, 22, 20, 18,
            10, 8, 5, 3, 0,
        ];

        foreach ($newUsersScores as $score) {
            $email = 'user_' . Str::random(8) . '@mail.com';

            $username = UsernameGenerator::generate();
            while(User::where('username', $username)->exists()) {
                $username = UsernameGenerator::generate();
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'password' => bcrypt('password123'),
                    'trust_score' => $score,
                    'role' => 'user'
                ]
            );

            $trustBadge = Badge::whereNotNull('trust_threshold')
                ->where('trust_threshold', '<=', $score)
                ->orderByDesc('trust_threshold')
                ->first();

            if ($trustBadge) {
                $user->badges()->syncWithoutDetaching([
                    $trustBadge->badge_id => ['awarded_at' => now()->subDays(rand(1, 120))]
                ]);
            }

            $user->updateBadges();
        }

        $this->command->info('Users seeded with realistic distribution using UsernameGenerator.');
    }
}
