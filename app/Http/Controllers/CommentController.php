<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\CommentSentimentLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index($postId)
    {
        $comments = Comment::with([
                'user',
                'parentComment.user',
                'replies' => function($q) {
                    $q->where('status', 'published')
                      ->orderBy('created_at', 'asc');
                },
                'replies.user',
                'replies.parentComment.user',
                'replies.replies'
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->oldest()
            ->get();

        $comments->loadMissing('replies.parentComment.user', 'replies.replies.parentComment.user');

        $payload = $comments->map(fn($c) => $this->formatComment($c));

        return response()->json($payload->values());
    }

    private function formatComment($comment)
    {
        return [
            'comment_id' => $comment->comment_id,
            'content'    => $comment->content,
            'created_at' => $comment->created_at ? $comment->created_at->toIso8601String() : null,
            'user'       => $comment->user ? [
                'user_id'  => $comment->user->user_id,
                'username' => $comment->user->username,
            ] : null,
            'parent_id'  => $comment->parent_id,
            'reply_to_user_id' => $comment->parentComment && $comment->parentComment->user
                ? $comment->parentComment->user->user_id
                : null,
            'reply_to'   => $comment->parentComment && $comment->parentComment->user
                ? $comment->parentComment->user->username
                : null,
            'replies'    => $comment->replies && $comment->replies->count()
                ? $comment->replies->map(fn($r) => $this->formatComment($r))->toArray()
                : [],
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id'   => 'required|exists:posts,post_id',
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,comment_id',
        ]);

        $sentimentLabel = 'POSITIVE';
        $confidence = 0.0;
        $isToxic = false;
        $status = 'published';

        try {
            $response = Http::timeout(2)->post('http://127.0.0.1:5000/analyze', [
                'text' => $request->content,
            ]);

            if ($response->successful()) {
                $aiResult = $response->json();
                $sentimentLabel = $aiResult['result'];
                $confidence = $aiResult['confidence'];

                if ($sentimentLabel === 'NEGATIVE') {
                    $isToxic = true;
                    $status = 'moderated';
                }
            }
        } catch (\Exception $e) {
            Log::warning("Sentiment AI Offline");
        }

        $comment = Comment::create([
            'post_id'         => $request->post_id,
            'user_id'         => auth()->id(),
            'content'         => $request->content,
            'parent_id'       => $request->parent_id,
            'sentiment_score' => $confidence,
            'status'          => $status,
        ]);

        CommentSentimentLog::create([
            'comment_id'      => $comment->comment_id,
            'sentiment_score' => $confidence,
            'result'          => $sentimentLabel,
            'created_at'      => now(),
        ]);

        if ($isToxic) {
            \App\Models\Report::create([
                'reporter_id'     => null,
                'reportable_id'   => $comment->comment_id,
                'reportable_type' => Comment::class,
                'reason'          => 'High Negative Sentiment',
                'details'         => "AI detected toxic comment (" . round($confidence * 100, 1) . "%).",
                'status'          => 'pending',
            ]);

            $comment->user->updateTrustScore(User::TRUST_SCORE_COMMENT_PENALTY, 'Toxic Comment Flagged');
        } else {
            $comment->user->updateTrustScore(User::TRUST_SCORE_COMMENT_REWARD, 'Comment Posted');
        }

        $comment->load(['user', 'parentComment.user', 'replies']);
        $responsePayload = $this->formatComment($comment);

        return response()->json([
            'data' => $responsePayload,
            'is_flagged' => $isToxic,
            'message' => $isToxic
                ? '⚠️ Comment submitted but held for moderation.'
                : 'Comment posted successfully!'
        ]);
    }

    public function destroy($id)
    {
        $comment = Comment::with('replies', 'user')->findOrFail($id);

        if (auth()->id() !== $comment->user_id && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($comment) {
            $this->deleteWithReplies($comment);
        });

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    private function deleteWithReplies($comment)
    {
        $comment->load('user', 'replies.user');

        foreach ($comment->replies as $reply) {
            $this->deleteWithReplies($reply);
        }

        if ($comment->user) {
            $comment->user->updateTrustScore(User::TRUST_SCORE_COMMENT_PENALTY, 'Comment Deleted');
        }

        $comment->delete();
    }

    public function report(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $comment = Comment::findOrFail($id);

        $report = \App\Models\Report::create([
            'reportable_type' => Comment::class,
            'reportable_id'   => $comment->comment_id,
            'user_id'         => $request->user()->user_id,
            'reason'          => $request->reason,
            'status'          => 'pending',
        ]);

        return response()->json([
            'message' => 'Comment reported successfully.',
            'report'  => $report,
        ]);
    }

    public function showReports($id)
    {
        $comment = Comment::findOrFail($id);
        $reports = \App\Models\Report::where('reportable_type', Comment::class)
            ->where('reportable_id', $id)
            ->get();

        return response()->json($reports);
    }
}
