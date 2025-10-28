<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
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
            'details' => 'nullable|string',
        ]);

        $report = new Report();
        $report->reporter_id = Auth::id();
        $report->reason = $request->reason;
        $report->details = $request->details;

        if ($request->type === 'user') {
            $report->reported_user_id = $request->target_id;
        } elseif ($request->type === 'post') {
            $report->post_id = $request->target_id;
        } elseif ($request->type === 'comment') {
            $report->comment_id = $request->target_id;
        }

        $report->save();

        return response()->json(['message' => 'Report submitted successfully!']);
    }

    public function reportUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $report = Report::create([
            'reporter_id' => auth()->id(),
            'reportable_id' => $user->user_id,
            'reportable_type' => User::class,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'User reported successfully', 'report' => $report]);
    }
}
