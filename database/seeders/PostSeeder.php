<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'user')->get();
        $categories = Category::all();
        $postIdeas = collect([
            [
                'title' => 'Starting a New Job – Any Advice?',
                'content' => 'I’m starting a new job soon and feeling both excited and nervous. For those who have been through this before, what helped you adjust during the first few weeks?'
            ],
            [
                'title' => 'How Do You Stay Productive When Motivation Drops?',
                'content' => 'Some days it feels impossible to stay productive. I’m curious what techniques or habits others use to stay on track when motivation is low.'
            ],
            [
                'title' => 'Is Remote Work Actually Better Than Office Work?',
                'content' => 'Remote work sounds great in theory, but I’ve noticed it comes with challenges too. What has your experience been like so far?'
            ],
            [
                'title' => 'Managing Stress During Busy Weeks',
                'content' => 'When deadlines pile up, stress can quickly build. I’d love to hear how others manage pressure without burning out.'
            ],
            [
                'title' => 'Best Ways to Save Money Each Month',
                'content' => 'I’ve been trying to be more mindful about spending. What are some realistic ways you’ve managed to save money consistently?'
            ],
            [
                'title' => 'Balancing Studies and Personal Life',
                'content' => 'Balancing academic responsibilities and personal life isn’t easy. How do you keep things from becoming overwhelming?'
            ],
            [
                'title' => 'What Makes an Online Community Feel Safe?',
                'content' => 'Some online spaces feel welcoming, while others don’t. What features or behaviors do you think help create a safer community?'
            ],
            [
                'title' => 'How Do You Handle Disagreements Online?',
                'content' => 'Disagreements are inevitable, especially online. I’m interested in how people handle conflict without escalating things.'
            ],
            [
                'title' => 'Staying Consistent With Personal Goals',
                'content' => 'Setting goals is easy, but staying consistent is hard. What strategies have actually worked for you long term?'
            ],
            [
                'title' => 'Your Experience With Online Learning',
                'content' => 'Online learning has become more common lately. What have been the biggest benefits and challenges for you?'
            ],
            [
                'title' => 'Dealing With Burnout',
                'content' => 'Burnout can creep up without warning. How do you recognize it early and recover before it gets worse?'
            ],
            [
                'title' => 'How Important Is Mental Health Awareness?',
                'content' => 'Mental health awareness is discussed more openly now. Do you think online communities play a role in supporting this?'
            ],
        ]);

        foreach ($users as $user) {
            if ($user->trust_score >= 80) {
                $postCount = rand(3, 5);
            } elseif ($user->trust_score >= 50) {
                $postCount = rand(1, 3);
            } elseif ($user->trust_score >= 20) {
                $postCount = rand(0, 1);
            } else {
                $postCount = 0;
            }

            for ($i = 0; $i < $postCount; $i++) {
                if ($postIdeas->isEmpty()) {
                    break;
                }

                $idea = $postIdeas->shift();

                $createdAt = Carbon::now()
                    ->subDays(rand(1, 120))
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(0, 59));

                Post::create([
                    'title' => $idea['title'],
                    'content' => $idea['content'],
                    'user_id' => $user->user_id,
                    'category_id' => $categories->random()->category_id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addMinutes(rand(5, 300)),
                ]);
            }
        }

        $this->command->info('Posts seeded with unique, realistic content and trust-based activity.');
    }
}
