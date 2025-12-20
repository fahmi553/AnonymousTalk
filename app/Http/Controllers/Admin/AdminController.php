<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Report;
use App\Models\Comment;
use App\Models\TrustScoreLog;

class AdminController extends Controller
{
    /**
     * Fetch data for the admin dashboard.
     */
    public function dashboard()
    {
        $pendingUserReports = Report::where('status', 'pending')->whereNotNull('reporter_id')->count();
        $pendingSentimentReports = Report::where('status', 'pending')->whereNull('reporter_id')->count();

        $stats = [
            'totalUsers' => User::count(),
            'totalPosts' => Post::count(),
            'pendingUserReports' => $pendingUserReports,
            'pendingSentimentReports' => $pendingSentimentReports,
        ];

        $userReports = Report::with(['reporter:user_id,username', 'reportable'])
            ->where('status', 'pending')
            ->whereNotNull('reporter_id')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($report) => $this->formatReport($report));

        $sentimentReports = Report::where('status', 'pending')
            ->whereNull('reporter_id')
            ->with('reportable')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($report) => $this->formatReport($report));

        return response()->json([
            'stats' => $stats,
            'userReports' => $userReports,
            'sentimentReports' => $sentimentReports,
        ]);
    }

    public function getFlaggedContent()
    {
        $reports = Report::where('status', 'pending')
            ->whereNull('reporter_id')
            ->with('reportable')
            ->latest()
            ->get()
            ->map(fn($report) => $this->formatReport($report));

        return response()->json($reports);
    }

    private function formatReport($report)
    {
        $type = 'Unknown';
        $contentSnippet = '';
        $postIdForComment = null;

        if ($report->reportable_type === Post::class || $report->reportable_type === 'App\\Models\\Post') {
            $type = 'Post';
            if ($report->reportable) {
                $contentSnippet = $report->reportable->title . ': ' . $report->reportable->content;
            }
        } elseif ($report->reportable_type === Comment::class || $report->reportable_type === 'App\\Models\\Comment') {
            $type = 'Comment';
            if ($report->reportable) {
                $contentSnippet = $report->reportable->content;
                $postIdForComment = $report->reportable->post_id;
            }
        } elseif ($report->reportable_type === User::class || $report->reportable_type === 'App\\Models\\User') {
            $type = 'User';
            if ($report->reportable) {
                $contentSnippet = "User Profile: " . $report->reportable->username;
            }
        }

        if (!$report->reportable) {
            $contentSnippet = "Content deleted or unavailable.";
        }

        return [
            'id' => $report->report_id,
            'type' => $type,
            'reported_by' => $report->reporter ? $report->reporter->username : 'Automated System',
            'reason' => $report->reason,
            'details' => $report->details,
            'content' => $contentSnippet,
            'reportable_id' => $report->reportable_id,
            'reportable_type' => $report->reportable_type,
            'reportable' => $report->reportable,
            'post_id_for_comment' => $postIdForComment,
            'status' => $report->status,
            'created_at' => $report->created_at,
        ];
    }

    public function showReportDetails($id)
    {
        $post = Post::with('categoryModel')->find($id);
        if ($post) {
            $reports = Report::with('reporter:user_id,username')
                ->where('reportable_type', Post::class)
                ->where('reportable_id', $id)
                ->get();

            return response()->json([
                'type' => 'Post',
                'content' => [
                    'title' => $post->title,
                    'body' => $post->content,
                    'category' => $post->categoryModel->name ?? 'None',
                    'author' => $post->user->username ?? 'Anonymous',
                    'status' => $post->status,
                ],
                'reports' => $reports
            ]);
        }

        $comment = Comment::with('user', 'post')->find($id);
        if ($comment) {
            $reports = Report::with('reporter:user_id,username')
                ->where('reportable_type', Comment::class)
                ->where('reportable_id', $id)
                ->get();

            return response()->json([
                'type' => 'Comment',
                'content' => [
                    'title' => 'Comment on Post #' . $comment->post_id,
                    'body' => $comment->content,
                    'category' => 'Comment',
                    'author' => $comment->user->username ?? 'Unknown',
                    'status' => $comment->status,
                ],
                'reports' => $reports
            ]);
        }

        return response()->json(['error' => 'Content not found'], 404);
    }

    /**
     * Delete a report.
     */
    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }

    public function getUserReportDetails($userId)
    {
        $user = User::where('user_id', $userId)->firstOrFail();
        $reports = Report::where('reportable_type', User::class)
            ->where('reportable_id', $userId)
            ->with('reporter:user_id,username')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user' => $user,
            'reports' => $reports
        ]);
    }

    /**
     * Handle moderation actions (Ban, Warn, Dismiss).
     */
    public function moderateUser($id, $action)
    {
        $user = User::findOrFail($id);

        switch ($action) {
            case 'dismiss':
                Report::where('reportable_type', User::class)
                      ->where('reportable_id', $id)
                      ->delete();
                $message = "Reports dismissed.";
                break;

            case 'warn':
                $user->applyTrustChange(
                    -10,
                    'Admin warning issued',
                    'admin_warning'
                );
                $message = "User has been warned.";
                break;

            case 'ban':
                $user->delete();
                $message = "User has been banned/deleted.";
                break;

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json(['message' => $message]);
    }

    public function getUsers(Request $request)
    {
        $query = User::query();
        $query->select('user_id', 'username', 'email', 'trust_score', 'created_at');
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('username', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        $users = $query->with('badges')
                    ->orderBy('user_id', 'desc')
                    ->paginate(10);

        return response()->json($users);
    }

    public function getSystemLogs()
    {
        $logs = TrustScoreLog::with('user:user_id,username,email')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($logs);
    }

    public function adjustTrustScore(Request $request, $id)
    {
        $request->validate([
            'score_change' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->increment('trust_score', $request->score_change);
        $user->checkForBadges();
        \App\Models\TrustScoreLog::create([
            'user_id' => $user->user_id,
            'action_type' => 'admin_adjustment',
            'score_change' => $request->score_change,
            'reason' => $request->reason,
            'timestamp' => now(),
        ]);
        $user->load('badges');

        return response()->json([
            'message' => 'Trust score updated successfully.',
            'new_score' => $user->trust_score,
            'badges' => $user->badges
        ]);
    }

    public function getFlaggedPosts()
    {
        $flaggedPosts = Post::with('user')
            ->where('status', 'moderated')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($flaggedPosts);
    }
}
