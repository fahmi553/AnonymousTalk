<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:user,post,comment',
            'target_id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        $modelMap = [
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
        ];

        if (!isset($modelMap[$request->type])) {
            return response()->json(['message' => 'Invalid report type'], 400);
        }

        $reportableType = $modelMap[$request->type];

        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', $reportableType)
            ->where('reportable_id', $request->target_id)
            ->exists();

        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this content. We are reviewing it.'
            ], 409);
        }

        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reason = $request->reason;
        $report->details = $request->details ?? $request->reason;
        $report->reportable_type = $reportableType;
        $report->reportable_id = $request->target_id;
        $report->status = 'pending';
        $report->save();

        return response()->json(['message' => 'Report submitted successfully!']);
    }

    public function reportUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'details' => 'nullable|string|max:1000'
        ]);

        $existingReport = Report::where('reporter_id', auth()->id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->user_id)
            ->exists();

        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this user.'
            ], 409);
        }

        $report = Report::create([
            'reporter_id' => auth()->id(),
            'reportable_id' => $user->user_id,
            'reportable_type' => User::class,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'User reported successfully', 'report' => $report]);
    }
}
