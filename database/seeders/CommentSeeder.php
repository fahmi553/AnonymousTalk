<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentSeeder extends Seeder
{
    public function run()
    {
        $posts = Post::all();
        $users = User::all();

        $sampleComments = [
            "I completely agree with you!",
            "That’s a great point, thanks for sharing.",
            "I’ve experienced something similar before.",
            "This is really helpful, appreciate it!",
            "Interesting perspective, I never thought of it that way.",
            "Could you share more details about that?",
            "I think it depends on the situation.",
            "Yes, I can relate to this so much.",
            "Thanks for posting this, it made my day.",
            "Haha, that’s so true!"
        ];

        foreach ($posts as $post) {
            $commentCount = rand(1, 5);
            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'post_id' => $post->post_id,
                    'user_id' => $users->random()->user_id,
                    'content' => $sampleComments[array_rand($sampleComments)]
                ]);
            }
        }
    }
}
