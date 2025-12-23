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
    public function run(): void
    {
        $reporters = User::where('role', 'user')->get();

        $posts = Post::all();
        $comments = Comment::all();
        $allUsers = User::all();

        if ($reporters->isEmpty() || $posts->isEmpty() || $comments->isEmpty()) {
            $this->command->warn('Skipping ReportSeeder: Insufficient data (users, posts, or comments missing).');
            return;
        }

        $this->command->info('Seeding realistic, context-aware reports...');

        $postScenarios = [
            'Spam' => [
                "User is flooding the 'Advice' category with links to a crypto scam site.",
                "This is clearly a bot. Look at the posting timestamp (5 posts in 1 minute).",
                "Advertisements are not allowed here. Please remove.",
                "Link redirects to a suspicious login page (phishing attempt).",
            ],
            'Hate Speech' => [
                "The title contains a slur targeting a specific ethnic group.",
                "This post is promoting violence and violates the community safety guidelines.",
                "User is using coded language/dogwhistles to bypass the filter.",
                "Extremely offensive imagery in the post body.",
            ],
            'Misinformation' => [
                "OP is spreading debunked conspiracy theories about the recent election.",
                "This health advice is dangerous and lacks scientific evidence.",
                "The quote attributed to the politician in this post is fake.",
            ],
            'Nudity/Sexual' => [
                "Image attached is NSFW and not tagged appropriately.",
                "Explicit sexual content in a general discussion thread.",
            ],
            'Self-Harm' => [
                "User is expressing suicidal ideation. Please intervene.",
                "Post encourages eating disorders/anorexia.",
            ]
        ];

        $commentScenarios = [
            'Harassment' => [
                "This user has replied to my last 3 comments calling me names.",
                "Doxing threat: They posted my real name in the thread.",
                "Persistent bullying. I've blocked them but they keep commenting.",
                "Violates the 'Be Respectful' policy. Personal attack.",
            ],
            'Trolling' => [
                "User is derailing the conversation with off-topic ranting.",
                "Deliberately trying to incite an argument in a support thread.",
                "Bumping old threads just to cause confusion.",
            ],
            'Spam' => [
                "Copy-pasting the same 'Follow me' message on every top comment.",
                "Link to a discord server spam.",
            ]
        ];

        $userScenarios = [
            'Impersonation' => [
                "This account is using the admin logo as their profile picture.",
                "Pretending to be a moderator and asking for passwords.",
                "Impersonating a known public figure.",
            ],
            'Inappropriate Profile' => [
                "Profile bio contains hate speech symbols.",
                "Username contains a racial slur.",
                "Avatar is an explicit image.",
            ]
        ];

        $systemLogs = [
            ['reason' => 'AI_SENTIMENT_FLAG', 'details' => 'Automated Flag: Toxicity Score 0.98 (High Confidence). Detected keywords: [threat, kill].'],
            ['reason' => 'RATE_LIMIT_EXCEEDED', 'details' => 'System Alert: User posted 15 comments in 60 seconds. Possible spambot.'],
            ['reason' => 'KEYWORDS_DETECTED', 'details' => 'Auto-Mod: Content matched blocked pattern /crypto|giveaway/i.'],
            ['reason' => 'EVASION_DETECTED', 'details' => 'System Alert: User attempting to obfuscate banned words (e.g., "b@d w0rd").'],
        ];

        $targetPost = $posts->random();
        for ($i = 0; $i < 3; $i++) {
            Report::create([
                'reporter_id' => $reporters->random()->user_id,
                'reportable_id' => $targetPost->post_id,
                'reportable_type' => Post::class,
                'reason' => 'Spam',
                'details' => Arr::random($postScenarios['Spam']),
                'status' => 'pending',
                'created_at' => now()->subMinutes(rand(1, 60)),
            ]);
        }

        $targetComment = $comments->random();
        Report::create([
            'reporter_id' => null,
            'reportable_id' => $targetComment->comment_id,
            'reportable_type' => Comment::class,
            'reason' => 'AI_SENTIMENT_FLAG',
            'details' => 'Automated Flag: Toxicity Score 0.92. Sentiment: Negative.',
            'status' => 'pending',
            'created_at' => now()->subHours(2),
        ]);
        Report::create([
            'reporter_id' => $reporters->random()->user_id,
            'reportable_id' => $targetComment->comment_id,
            'reportable_type' => Comment::class,
            'reason' => 'Harassment',
            'details' => 'They are being extremely aggressive and rude.',
            'status' => 'pending',
            'created_at' => now()->subHours(1),
        ]);
        for ($i = 0; $i < 20; $i++) {
            $reasonCategory = array_rand($postScenarios);
            $detailText = Arr::random($postScenarios[$reasonCategory]);

            Report::create([
                'reporter_id' => $reporters->random()->user_id,
                'reportable_id' => $posts->random()->post_id,
                'reportable_type' => Post::class,
                'reason' => $reasonCategory,
                'details' => $detailText,
                'status' => Arr::random(['pending', 'pending', 'reviewed', 'resolved']),
                'created_at' => now()->subDays(rand(0, 7)),
            ]);
        }

        for ($i = 0; $i < 15; $i++) {
            $reasonCategory = array_rand($commentScenarios);
            $detailText = Arr::random($commentScenarios[$reasonCategory]);

            Report::create([
                'reporter_id' => $reporters->random()->user_id,
                'reportable_id' => $comments->random()->comment_id,
                'reportable_type' => Comment::class,
                'reason' => $reasonCategory,
                'details' => $detailText,
                'status' => 'pending',
                'created_at' => now()->subDays(rand(0, 3)),
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            $reasonCategory = array_rand($userScenarios);
            $detailText = Arr::random($userScenarios[$reasonCategory]);

            $reporter = $reporters->random();
            $target = $allUsers->where('user_id', '!=', $reporter->user_id)->random();

            Report::create([
                'reporter_id' => $reporter->user_id,
                'reportable_id' => $target->user_id,
                'reportable_type' => User::class,
                'reason' => $reasonCategory,
                'details' => $detailText,
                'status' => 'pending',
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            $log = Arr::random($systemLogs);
            Report::create([
                'reporter_id' => null,
                'reportable_id' => $posts->random()->post_id,
                'reportable_type' => Post::class,
                'reason' => $log['reason'],
                'details' => $log['details'],
                'status' => 'pending',
                'created_at' => now()->subHours(rand(1, 12)),
            ]);
        }

        $this->command->info('Legitimate-looking reports seeded successfully!');
    }
}
