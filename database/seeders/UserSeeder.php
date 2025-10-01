<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'Admin',
                'password' => bcrypt('Qawsedrftgyh456@'),
                'trust_score' => 100,
                'role' => 'admin'
            ]
        );

        // Regular users
        $users = [
            ['ali@gmail.com', 'BraveOwl659·', 80],
            ['sara@gmail.com', 'CleverWolf366', 75],
            ['john@gmail.com', 'BrightWolf818', 85],
            ['lisa@gmail.com', 'BraveEagle219', 90],
            ['mike@gmail.com', 'MysteriousEagle726', 70],
        ];

        foreach ($users as [$email, $username, $score]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'password' => bcrypt('Qawsedrftgyh456@'),
                    'trust_score' => $score,
                    'role' => 'user'
                ]
            );
        }
    }
}
