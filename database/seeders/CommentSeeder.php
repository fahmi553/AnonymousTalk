<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $posts = Post::all();

        $comments = [
            'Great post! I totally agree with you.',
            'I had a similar experience and this really helped me.',
            'Thanks for sharing, very insightful!',
            'This is something I’ve been thinking about as well.',
            'Interesting perspective, I’ll keep this in mind.'
        ];

        foreach ($posts as $post) {
            foreach ($comments as $text) {
                Comment::create([
                    'post_id' => $post->post_id,
                    'user_id' => $user->user_id,
                    'content' => $text
                ]);
            }
        }
    }
}
