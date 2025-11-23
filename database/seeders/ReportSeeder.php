<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Support\Arr;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get Data Providers
        // UPDATED: Filter using the 'role' column from your table schema
        $regularUsers = User::where('role', 'user')->get();
        
        $posts = Post::all();
        $comments = Comment::all();
        $allUsers = User::all(); // Targets can be anyone (even admins can be reported!)

        // Safety check to prevent errors if database is empty
        if ($regularUsers->isEmpty() || $posts->isEmpty() || $comments->isEmpty()) {
            $this->command->warn('Skipping Report Seeder: Need at least 1 regular user (role="user"), posts, and comments.');
            return;
        }

        $this->command->info('Seeding realistic reports...');

        // ==========================================
        // DATA SETS FOR REALISM
        // ==========================================

        $postReasons = [
            'Spam' => ['Posted a link to a crypto scam site.', 'Repeatedly posting the same advertisement.', 'Bot behavior detected.'],
            'Hate Speech' => ['Used racial slurs in the title.', 'Promoting violence against a specific group.', 'Offensive symbols in the post image.'],
            'Misinformation' => ['Sharing fake news regarding health.', 'Conspiracy theories without basis.', 'Misleading political claims.'],
            'Nudity/Sexual' => ['Contains sexually explicit content.', 'Inappropriate image for a general audience.'],
            'Self-Harm' => ['User is threatening to hurt themselves.', 'Encouraging eating disorders.'],
        ];

        $commentReasons = [
            'Harassment' => ['Bullying the OP.', 'Personal attacks and name-calling.', 'Stalking behavior across multiple posts.'],
            'Spam' => ['Just commented "Follow me" on every post.', 'Link to a phishing site.'],
            'Trolling' => ['Deliberately trying to start an argument.', 'Off-topic ranting.'],
        ];

        $userReasons = [
            'Impersonation' => ['Pretending to be a moderator.', 'Using a celebrity photo and name.', 'Impersonating another user.'],
            'Inappropriate Profile' => ['Profile picture contains nudity.', 'Bio contains hate speech.', 'Username is offensive.'],
        ];

        // ==========================================
        // 1. SPECIFIC SCENARIOS
        // ==========================================

        // Scenario A: The Crypto Bot (Regular user reports a post)
        $scamPost = $posts->random();
        Report::create([
            'reporter_id' => $regularUsers->random()->user_id,
            'reportable_id' => $scamPost->post_id,
            'reportable_type' => Post::class,
            'reason' => 'Spam',
            'details' => 'This user is promising free Bitcoin if I click the link.',
            'status' => 'pending',
        ]);
        
        // Scenario B: Automated System Flag (No Reporter)
        Report::create([
            'reporter_id' => null, // System generated
            'reportable_id' => $posts->random()->post_id,
            'reportable_type' => Post::class,
            'reason' => 'Automated Sentiment Flag',
            'details' => 'System detected highly negative sentiment (-0.95) and flagged for review.',
            'status' => 'pending',
        ]);

        // Scenario C: User Reporting a Profile
        // Ensure the target is not the reporter
        $reporter = $regularUsers->random();
        $targetUser = $allUsers->where('user_id', '!=', $reporter->user_id)->random();

        Report::create([
            'reporter_id' => $reporter->user_id,
            'reportable_id' => $targetUser->user_id,
            'reportable_type' => User::class,
            'reason' => 'Inappropriate Profile',
            'details' => 'Their profile picture is graphic violence.',
            'status' => 'pending',
        ]);

        // ==========================================
        // 2. GENERATE RANDOM VOLUME (Loop)
        // ==========================================
        
        // Generate 15 random Post reports
        for ($i = 0; $i < 15; $i++) {
            $category = array_rand($postReasons);
            $detail = Arr::random($postReasons[$category]);
            
            Report::create([
                'reporter_id' => $regularUsers->random()->user_id,
                'reportable_id' => $posts->random()->post_id,
                'reportable_type' => Post::class,
                'reason' => $category,
                'details' => $detail,
                'status' => Arr::random(['pending', 'pending', 'pending', 'reviewed']), // Mostly pending
            ]);
        }

        // Generate 10 random Comment reports
        for ($i = 0; $i < 10; $i++) {
            $category = array_rand($commentReasons);
            $detail = Arr::random($commentReasons[$category]);

            Report::create([
                'reporter_id' => $regularUsers->random()->user_id,
                'reportable_id' => $comments->random()->comment_id,
                'reportable_type' => Comment::class,
                'reason' => $category,
                'details' => $detail,
                'status' => 'pending',
            ]);
        }

        // Generate 5 random User reports
        for ($i = 0; $i < 5; $i++) {
            $category = array_rand($userReasons);
            $detail = Arr::random($userReasons[$category]);
            
            $reporter = $regularUsers->random();
            // Prevent reporting self
            $target = $allUsers->where('user_id', '!=', $reporter->user_id)->random();

            Report::create([
                'reporter_id' => $reporter->user_id,
                'reportable_id' => $target->user_id,
                'reportable_type' => User::class,
                'reason' => $category,
                'details' => $detail,
                'status' => 'pending',
            ]);
        }

        // Generate 3 System Flags (Automated)
        for ($i = 0; $i < 3; $i++) {
            Report::create([
                'reporter_id' => null,
                'reportable_id' => $posts->random()->post_id,
                'reportable_type' => Post::class,
                'reason' => 'System Flag: Keywords',
                'details' => 'Detected prohibited keywords related to self-harm.',
                'status' => 'pending',
            ]);
        }

        $this->command->info('Reports seeded successfully!');
    }
}