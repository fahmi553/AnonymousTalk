<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        DB::table('user_badges')->delete();
        DB::table('badges')->delete();

        $now = now();

        DB::table('badges')->insert([
        ]);

        $this->command->info('Badges seeded successfully.');
    }
}
