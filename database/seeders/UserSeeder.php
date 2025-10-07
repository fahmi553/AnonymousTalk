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
            ['ali@gmail.com', 'BoldCat596', 80],
            ['sara@gmail.com', 'ResilientAntelope916', 75],
            ['john@gmail.com', 'UniqueNewt25', 85],
            ['lisa@gmail.com', 'NimbleAntelope184', 90],
            ['mike@gmail.com', 'IntrepidGiraffe90', 70],
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
