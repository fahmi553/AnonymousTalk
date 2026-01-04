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
        $users = User::where('role', 'user')->get();

        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No posts or users found. Seed users and posts first.');
            return;
        }

        $commentPool = collect([
            "I really relate to this. It’s something I’ve struggled with too, and seeing others talk about it helps a lot.",
            "This is a thoughtful post. I think many people underestimate how common this experience actually is.",
            "I went through something similar last year, and what helped most was taking things one step at a time.",
            "You explained this really clearly. It made me rethink how I approach similar situations.",
            "I don’t fully agree, but I appreciate how respectfully you presented your point.",
            "This is a topic that doesn’t get discussed enough. Thanks for bringing it up.",
            "I’ve had mixed experiences with this, so it’s interesting to hear a different perspective.",
            "Reading this made me pause and reflect on my own habits.",
            "I think the key issue here is balance. Too much of either side can cause problems.",
            "This resonates a lot with what I’m dealing with right now."
        ]);

        $replyPool = collect([
            "That’s a really good point.",
            "I agree with you on this.",
            "I hadn’t thought about it that way before.",
            "That makes a lot of sense.",
            "Thanks for explaining further.",
            "I see where you’re coming from.",
            "That’s an interesting way to look at it."
        ]);

        foreach ($posts as $post) {

            $baseComments = rand(2, 4);

            for ($i = 0; $i < $baseComments; $i++) {

                if ($commentPool->isEmpty()) {
                    break;
                }

                $user = $this->pickUserByTrust($users);

                $createdAt = Carbon::parse($post->created_at)
                    ->addMinutes(rand(10, 1440));

                $comment = Comment::create([
                    'post_id' => $post->post_id,
                    'user_id' => $user->user_id,
                    'content' => $commentPool->shift(),
                    'parent_id' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addMinutes(rand(1, 120)),
                ]);

                $this->seedReplies($comment, $users, $replyPool);
            }
        }

        $this->command->info('Comments seeded with realistic, unique discussion threads.');
    }

    private function seedReplies(Comment $parent, $users, $replyPool)
    {
        if ($replyPool->isEmpty()) return;

        $replyCount = rand(0, 2);

        for ($i = 0; $i < $replyCount; $i++) {

            if ($replyPool->isEmpty()) break;

            $user = $this->pickUserByTrust($users);

            $createdAt = Carbon::parse($parent->created_at)
                ->addMinutes(rand(5, 180));

            Comment::create([
                'post_id' => $parent->post_id,
                'user_id' => $user->user_id,
                'content' => $replyPool->shift(),
                'parent_id' => $parent->comment_id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addMinutes(rand(1, 60)),
            ]);
        }
    }

    private function pickUserByTrust($users)
    {
        return $users
            ->sortByDesc('trust_score')
            ->take(rand(3, $users->count()))
            ->random();
    }
}
