<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Report;
use App\Models\Comment;
use App\Models\TrustScoreLog;
use App\Notifications\AdminActionNotification;

class AdminController extends Controller
{
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

    public function moderateUser(Request $request, $id, $action)
    {
        $user = User::findOrFail($id);

        if (in_array($action, ['warn', 'ban'])) {
            $request->validate(['reason' => 'required|string|max:255']);
        }

        $message = '';

        switch ($action) {
            case 'dismiss':
                Report::where('reportable_type', User::class)
                    ->where('reportable_id', $id)
                    ->update(['status' => 'resolved']);
                $message = "Reports dismissed.";
                break;

            case 'warn':
                $user->applyTrustChange(
                    -10,
                    "Admin Warning: " . $request->reason,
                    'admin_warning'
                );

                $user->notify(new AdminActionNotification('warning', "You received an official warning: " . $request->reason));

                $message = "User has been warned and notified.";
                break;

            case 'ban':
                $user->banned_at = now();
                $user->ban_reason = $request->reason;
                $user->ban = 1;
                $user->save();
                $message = "User has been banned.";
                break;

            case 'unban':
                $user->banned_at = null;
                $user->ban_reason = null;
                $user->ban = 0;
                $user->save();
                $message = "User has been unbanned.";
                break;

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json([
            'message' => $message,
            'user_status' => [
                'trust_score' => $user->trust_score,
                'banned_at' => $user->banned_at
            ]
        ]);
    }

    public function getUsers(Request $request)
    {
        $query = User::query();
        $query->select('user_id', 'username', 'email', 'trust_score', 'created_at', 'banned_at');
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('username', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        $perPage = $request->input('per_page', 10);
        $users = $query->with('badges')
                   ->orderBy('user_id', 'desc')
                   ->paginate($perPage);

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

        $user->applyTrustChange(
            (int) $request->score_change,
            $request->reason,
            'admin_adjustment'
        );

        $type = $request->score_change < 0 ? 'warning' : 'info';
        $sign = $request->score_change > 0 ? '+' : '';
        $message = "Your Trust Score was adjusted by an Admin ({$sign}{$request->score_change}). Reason: {$request->reason}";

        $user->notify(new AdminActionNotification($type, $message));

        return response()->json([
            'message' => 'Trust score updated successfully.',
            'new_score' => $user->trust_score,
            'is_banned' => !is_null($user->banned_at)
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

    public function getContent(Request $request)
    {
        $type = $request->query('type', 'posts');
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        if ($type === 'posts') {
            $query = Post::query();
            $query->withoutGlobalScopes();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"));
                });
            }
            $query->with('user')->withCount('reports');

        } else {
            $query = Comment::query();
            $query->withoutGlobalScopes();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('content', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"));
                });
            }
            $query->with('user', 'post')->withCount('reports');
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->latest()->paginate(15);

        return response()->json($items);
    }

    public function getReportSummary()
    {
        return response()->json([
            'reportsByType' => Report::select('reportable_type')
                ->selectRaw('count(*) as total')
                ->groupBy('reportable_type')
                ->get(),

            'reportsByStatus' => Report::select('status')
                ->selectRaw('count(*) as total')
                ->groupBy('status')
                ->get(),

            'topReportedUsers' => Report::where('reportable_type', User::class)
                ->select('reportable_id')
                ->selectRaw('count(*) as total')
                ->groupBy('reportable_id')
                ->orderByDesc('total')
                ->take(5)
                ->get()
        ]);
    }

    public function moderateContent(Request $request, $type, $id, $action)
    {
        $type = \Illuminate\Support\Str::singular($type);

        $modelClass = $type === 'post' ? \App\Models\Post::class : \App\Models\Comment::class;
        $content = $modelClass::findOrFail($id);

        $previousStatus = $content->status;

        switch ($action) {
            case 'approve':
                $content->status = 'published';
                $content->save();

                if ($previousStatus !== 'published') {

                    if ($type === 'post' && $content->user) {
                        $content->user->applyTrustChange(2, 'Post Approved by Admin', 'post_approved');
                    }
                    elseif ($type === 'comment' && $content->user) {
                        $content->user->applyTrustChange(1, 'Comment Approved by Admin', 'comment_approved');
                    }

                    $content->user->updateBadges();
                }
                break;

            case 'reject':
                $content->status = 'deleted';
                $content->save();
                if ($previousStatus === 'published' && $content->user) {

                    if ($type === 'post') {
                        $content->user->applyTrustChange(\App\Models\User::TRUST_SCORE_POST_PENALTY, 'Post Deleted by Admin (Report Valid)', 'admin_moderation');
                    }
                    elseif ($type === 'comment') {
                        $content->user->applyTrustChange(\App\Models\User::TRUST_SCORE_COMMENT_PENALTY, 'Comment Deleted by Admin (Report Valid)', 'admin_moderation');
                    }

                    $content->user->updateBadges();
                }
                break;

            case 'hide':
                $content->status = 'moderated';
                $content->save();
                if ($previousStatus === 'published' && $content->user) {
                      $points = $type === 'post' ? -2 : -1;
                      $content->user->applyTrustChange($points, ucfirst($type) . ' Hidden by Admin', 'admin_moderation');
                      $content->user->updateBadges();
                }
                break;
        }

        \App\Models\Report::where('reportable_id', $id)
            ->where('reportable_type', $modelClass)
            ->where('status', 'pending')
            ->update(['status' => 'resolved']);

        return response()->json(['message' => "Content processed successfully."]);
    }
}
