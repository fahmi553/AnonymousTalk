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
        $users = User::all();
        $categories = Category::pluck('name')->toArray();

        $samplePosts = [
            "Need Advice on Starting a New Job",
            "Best Movies to Watch This Month",
            "Tips for Staying Motivated in College",
            "What's Your Favorite Local Food?",
            "How to Save Money on Groceries",
            "Is It Worth Upgrading to the Latest iPhone?",
            "Fun Weekend Activities Around Town",
            "How to Deal with Stress at Work",
            "Your Thoughts on Remote Learning?",
            "Share Your Travel Experiences!"
        ];

        $sampleContents = [
            "I'm starting a new chapter in my life and would love to hear from people with experience.",
            "Looking for recommendations — what should I watch next?",
            "Sometimes it’s hard to stay motivated, especially during exam season.",
            "There are so many great dishes locally, but I can’t decide my favorite.",
            "Trying to cut costs this month — any money-saving hacks?",
            "Thinking about buying the new model but not sure if it’s worth it.",
            "Need ideas for fun things to do with friends this weekend.",
            "Work has been really stressful lately, how do you cope?",
            "Online classes have their pros and cons, what’s your take?",
            "I had an amazing trip last month — where should I go next?",
            "Starting a new job feels exciting but also overwhelming at the same time. I’m not sure what to expect on the first day, and I’d love to hear some advice from those of you who have been in this position. How did you prepare, and what made the biggest difference in adjusting to the new environment?",
            "Remote learning has completely changed how we approach education. While it offers flexibility and the chance to learn from anywhere, I often feel disconnected from my classmates and instructors. Sometimes, it’s hard to stay disciplined and motivated when there’s no physical classroom environment.",
            "Traveling is one of my greatest passions. Last year, I had the opportunity to visit several countries and immerse myself in different cultures. The experiences were unforgettable, from trying new foods to learning local traditions. I’d love to hear about your travel stories as well!"
        ];

        for ($i = 0; $i < 20; $i++) {
            $randomTime = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            Post::create([
                'title' => $samplePosts[array_rand($samplePosts)],
                'content' => $sampleContents[array_rand($sampleContents)],
                'category_id' => Category::inRandomOrder()->first()->category_id,
                'user_id' => $users->random()->user_id,
                'created_at' => $randomTime,
                'updated_at' => $randomTime->copy()->addMinutes(rand(5, 500))
            ]);
        }
    }
}
