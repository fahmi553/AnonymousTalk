<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('user_id'); 
        $posts = Post::pluck('post_id');

        if ($userIds->isEmpty() || $posts->isEmpty()) {
            $this->command->info('No users or posts found. Skipping LikeSeeder.');
            return;
        }

        $this->command->info('Seeding likes... this might take a moment.');

        $likesData = [];
        $batchSize = 500; 

        foreach ($posts as $postId) {
            $likeCount = rand(0, min(20, $userIds->count()));

            if ($likeCount === 0) continue;

            $randomUserIds = $userIds->random($likeCount);

            foreach ($randomUserIds as $userId) {
                $likesData[] = [
                    'post_id'    => $postId,
                    'user_id'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($likesData) >= $batchSize) {
                    DB::table('likes')->insertOrIgnore($likesData);
                    $likesData = []; 
                }
            }
        }

        if (!empty($likesData)) {
            DB::table('likes')->insertOrIgnore($likesData);
        }

        $this->command->info('Likes seeded successfully!');
    }
}