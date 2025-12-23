<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $avatars = [
            'default.jpg',
            'avatar1.jpg',
            'avatar2.jpg',
            'avatar3.jpg',
            'avatar4.jpg',
            'avatar5.jpg',
            'avatar6.jpg',
            'avatar7.jpg',
            'avatar8.jpg',
            'avatar9.jpg',
        ];

        $users = User::all();

        $this->command->info("Assigning avatars to {$users->count()} users...");

        foreach ($users as $user) {
            $randomAvatar = $avatars[array_rand($avatars)];

            $user->avatar = $randomAvatar;
            $user->save();
        }

        $this->command->info('All users now have avatars!');
    }
}
