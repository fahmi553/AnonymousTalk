<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Report;
use App\Models\Comment;

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
        $userReports = Report::with('reporter:user_id,username')
            ->where('status', 'pending')
            ->whereNotNull('reporter_id')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($report) => $this->formatReport($report));
        $sentimentReports = Report::where('status', 'pending')
            ->whereNull('reporter_id')
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
    private function formatReport($report)
    {
        $type = 'Unknown';
        $postIdForComment = null;

        if ($report->reportable_type === Post::class) {
            $type = 'Post';
        } elseif ($report->reportable_type === Comment::class) {
            $type = 'Comment';
            $comment = Comment::find($report->reportable_id);
            if ($comment) {
                $postIdForComment = $comment->post_id;
            }
        } elseif ($report->reportable_type === User::class) {
            $type = 'User';
        }

        return [
            'id' => $report->report_id,
            'type' => $type,
            'reported_by' => $report->reporter ? $report->reporter->username : 'Automated System',
            'reason' => $report->reason,
            'reportable_id' => $report->reportable_id,
            'reportable_type' => $report->reportable_type,
            'post_id_for_comment' => $postIdForComment,
            'status' => $report->status,
        ];
    }
    public function showReportDetails($postId)
    {
        $post = Post::with('categoryModel')->findOrFail($postId);
        $reports = Report::with('reporter:user_id,username')
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $postId)
            ->where('status', 'pending')
            // ->whereNotNull('reporter_id')
            ->get();

        return response()->json([
            'post' => [
                'title' => $post->title,
                'content' => $post->content,
                'category' => $post->categoryModel->name ?? 'None',
            ],
            'reports' => $reports
        ]);
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
}
