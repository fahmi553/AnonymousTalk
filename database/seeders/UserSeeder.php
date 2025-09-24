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
                'username' => 'admin',
                'password' => bcrypt('Qawsedrftgyh456@'),
                'trust_score' => 100,
                'role' => 'admin'
            ]
        );

        // Regular users
        $users = [
            ['ali@gmail.com', 'ali', 80],
            ['sara@gmail.com', 'sara', 75],
            ['john@gmail.com', 'john', 85],
            ['lisa@gmail.com', 'lisa', 90],
            ['mike@gmail.com', 'mike', 70],
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
