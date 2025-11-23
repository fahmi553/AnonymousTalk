<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        DB::table('badges')->delete();

        $badges = [
            [
                'badge_name' => 'Verified Member',
                'description' => 'A user who has shown consistent positive behavior.',
                'trust_threshold' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'badge_name' => 'Trusted Contributor',
                'description' => 'Highly trusted member of the community.',
                'trust_threshold' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'badge_name' => 'Community Guardian',
                'description' => 'Top-tier user who helps moderate the platform.',
                'trust_threshold' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('badges')->insert($badges);
        $this->command->info('Badges seeded successfully.');
    }
}