<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Comment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     $this->call([
    //         UserSeeder::class,
    //         PostSeeder::class,
    //         CommentSeeder::class,
    //     ]);
    // }
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'admin',
                'password' => bcrypt('Qawsedrftgyh456@'),
                'trust_score' => 100,
                'role' => 'admin'
            ]
        );
        $user = User::firstOrCreate(
            ['email' => 'ali@gmail.com'],
            [
                'username' => 'ali',
                'password' => bcrypt('Qawsedrftgyh456@'),
                'trust_score' => 80,
                'role' => 'user'
            ]
        );
        $posts = [
            [
                'title' => 'Need Advice on Starting a New Job',
                'content' => 'I am starting a new job next week and feeling both excited and nervous. Any tips to make a good first impression?',
                'category' => 'Advice',
                'user_id' => $user->user_id
            ],
            [
                'title' => 'Best Movies of 2025',
                'content' => 'What are your favorite movies released this year? I’m looking for recommendations.',
                'category' => 'Entertainment',
                'user_id' => $user->user_id
            ]
        ];

        foreach ($posts as $postData) {
            $post = Post::create($postData);
            Comment::create([
                'post_id' => $post->post_id,
                'user_id' => $admin->user_id,
                'content' => 'Great post! I totally agree with you.'
            ]);

            Comment::create([
                'post_id' => $post->post_id,
                'user_id' => $user->user_id,
                'content' => 'Thanks for sharing, this is helpful!'
            ]);
        }
    }
}
