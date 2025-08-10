<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'trust_score' => 100,
            'role' => 'admin'
        ]);

        $posts = [
            [
                'title' => 'Need Advice on Starting a New Job',
                'content' => 'I am starting a new job next week and feeling both excited and nervous. Any tips to make a good first impression?',
                'category' => 'Advice',
            ],
            [
                'title' => 'Favorite Places to Travel?',
                'content' => 'I love visiting nature spots and hiking trails. Where are your favorite places to go for a relaxing trip?',
                'category' => 'Travel',
            ],
            [
                'title' => 'Struggling with Time Management',
                'content' => 'I find it hard to balance work, study, and personal time. How do you stay organized and productive?',
                'category' => 'Lifestyle',
            ],
        ];

        foreach ($posts as $post) {
            Post::create([
                'user_id' => $user->user_id,
                'title' => $post['title'],
                'content' => $post['content'],
                'category' => $post['category'],
            ]);
        }
    }
}
