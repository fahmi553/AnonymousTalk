<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;

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
            "I had an amazing trip last month — where should I go next?"
        ];

        for ($i = 0; $i < 20; $i++) {
            Post::create([
                'title' => $samplePosts[array_rand($samplePosts)],
                'content' => $sampleContents[array_rand($sampleContents)],
                'category_id' => Category::inRandomOrder()->first()->category_id,
                'user_id' => $users->random()->user_id
            ]);
        }
    }
}
