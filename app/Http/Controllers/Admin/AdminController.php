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
        $stats = [
            'totalUsers' => User::count(),
            'totalPosts' => Post::count(),
            'totalReports' => Report::where('status', 'pending')->count(),
        ];

        $reports = Report::with('reporter:user_id,username')
            ->where('status', 'pending')
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($report) {
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
                    'reported_by' => $report->reporter ? $report->reporter->username : 'Unknown',
                    'reason' => $report->reason,

                    'reportable_id' => $report->reportable_id,
                    'reportable_type' => $report->reportable_type,
                    'post_id_for_comment' => $postIdForComment,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'reports' => $reports
        ]);
    }
    public function showReportDetails($postId)
    {
        $post = Post::with('categoryModel')->findOrFail($postId);
        $reports = Report::with('reporter:user_id,username')
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $postId)
            ->where('status', 'pending')
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
