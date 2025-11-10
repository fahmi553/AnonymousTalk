<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Added missing models
use App\Models\User;
use App\Models\Post;
use App\Models\Report;
use App\Models\Comment; // <-- THIS WAS THE MISSING LINE

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
            'totalReports' => Report::where('status', 'pending')->count(), // Only count pending reports
        ];

        // Eager load the user who reported
        $reports = Report::with('reporter')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($report) {
                // Determine reportable type for a clean 'Type' column
                $type = 'Unknown';
                if ($report->reportable_type === Post::class) {
                    $type = 'Post';
                } elseif ($report->reportable_type === Comment::class) {
                    $type = 'Comment';
                } elseif ($report->reportable_type === User::class) {
                    $type = 'User';
                }

                return [
                    'id' => $report->report_id, // Make sure to use 'report_id' if that's your primary key
                    'type' => $type,
                    'reported_by' => $report->reporter->username ?? 'Unknown',
                    'reason' => $report->reason,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'reports' => $reports
        ]);
    }

    /**
     * Delete a report.
     */
    public function deleteReport($id)
    {
        // Use 'report_id' if that is your primary key
        $report = Report::where('report_id', $id)->firstOrFail();
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}

