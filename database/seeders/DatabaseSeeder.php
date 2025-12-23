<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
            ReportSeeder::class,
            TrustScoreLogSeeder::class,
            BadgeSeeder::class,
        ]);

        \App\Models\User::all()->each->updateBadges();
    }
}
