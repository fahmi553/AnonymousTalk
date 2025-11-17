<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();

        if ($users->count() < 2 || $posts->isEmpty() || $comments->isEmpty()) {
            $this->command->info('Could not create reports. Please seed Users, Posts, and Comments first.');
            return;
        }

        $this->command->info('Seeding Reports...');
        Report::create([
            'reporter_id' => $users->first()->user_id,
            'reportable_id' => $posts->first()->post_id,
            'reportable_type' => Post::class,
            'reason' => 'Spam',
            'status' => 'pending',
        ]);
        Report::create([
            'reporter_id' => $users->last()->user_id,
            'reportable_id' => $posts->first()->post_id,
            'reportable_type' => Post::class,
            'reason' => 'This is just hate speech.',
            'status' => 'pending',
        ]);
        Report::create([
            'reporter_id' => $users->first()->user_id,
            'reportable_id' => $comments->first()->comment_id,
            'reportable_type' => Comment::class,
            'reason' => 'Harassment',
            'status' => 'pending',
        ]);
        Report::create([
            'reporter_id' => $users->first()->user_id,
            'reportable_id' => $users->last()->user_id,
            'reportable_type' => User::class,
            'reason' => 'This user has an inappropriate username.',
            'status' => 'pending',
        ]);
    }
}
