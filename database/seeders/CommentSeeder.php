<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;

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
            "Haha, that’s so true!",
            "I think your post touches on something that many people are experiencing nowadays. Personally, I’ve gone through something very similar and what helped me was breaking things down into smaller, more manageable tasks instead of trying to do everything at once.",
            "This is such an insightful perspective. I’ve always struggled with finding balance in situations like this, but reading your comment made me realize there are other approaches I haven’t tried yet. Thanks a lot for sharing your experience in such detail.",
            "Honestly, this is one of the most relatable posts I’ve seen in a while. It reminded me of a time when I faced the same challenge and the only thing that helped was reaching out to friends and talking it through."
        ];

        if ($users->isEmpty() || $posts->isEmpty()) {
            $this->command->warn('No users or posts found. Seed users and posts first.');
            return;
        }

        foreach ($posts as $post) {
            $commentCount = rand(2, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                $randomTime = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                $topComment = Comment::create([
                    'post_id' => $post->post_id,
                    'user_id' => $users->random()->user_id,
                    'content' => $sampleComments[array_rand($sampleComments)],
                    'parent_id' => null,
                    'created_at' => $randomTime,
                    'updated_at' => $randomTime->copy()->addMinutes(rand(5, 500)),
                ]);

                $this->createReplies($topComment, $users, $post, $sampleComments, 0);
            }
        }
    }

    private function createReplies($comment, $users, $post, $sampleComments, $depth)
    {
        if ($depth >= 3) {
            return;
        }

        $replyCount = rand(0, 3);

        for ($i = 0; $i < $replyCount; $i++) {
            $randomTime = Carbon::parse($comment->created_at)->addMinutes(rand(1, 1440));

            $reply = Comment::create([
                'post_id' => $post->post_id,
                'user_id' => $users->random()->user_id,
                'content' => $sampleComments[array_rand($sampleComments)],
                'parent_id' => $comment->comment_id,
                'created_at' => $randomTime,
                'updated_at' => $randomTime->copy()->addMinutes(rand(1, 200)),
            ]);

            $this->createReplies($reply, $users, $post, $sampleComments, $depth + 1);
        }
    }
}
