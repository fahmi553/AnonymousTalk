<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Qawsedrftgyh456@'),
            'trust_score' => 100,
            'role' => 'admin',
            'badge_id' => null,
        ]);
        User::create([
            'username' => 'ali',
            'email' => 'ali@gmail.com',
            'password' => Hash::make('Qawsedrftgyh456@'),
            'trust_score' => 10,
            'role' => 'user',
            'badge_id' => null,
        ]);
    }
}
