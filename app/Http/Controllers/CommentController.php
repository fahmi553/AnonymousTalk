<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\CommentSentimentLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewCommentNotification;

class CommentController extends Controller
{
    public function index($postId)
    {
        $comments = Comment::with([
                'user:user_id,username,avatar',
                'user.badges',
                'parentComment.user:user_id,username,avatar',
                'parentComment.user.badges',
                'replies' => function($q) {
                    $q->where('status', 'published')
                    ->orderBy('created_at', 'asc');
                },
                'replies.user:user_id,username,avatar',
                'replies.user.badges',
                'replies.parentComment.user.badges',
                'replies.replies'
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->oldest()
            ->get();

        $comments->loadMissing('replies.parentComment.user.badges', 'replies.replies.parentComment.user.badges');

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
                'avatar' => $comment->user->avatar ?? 'default.jpg',
                'badges'   => $comment->user->badges->map(fn($b) => [
                    'badge_id'       => $b->badge_id,
                    'badge_name'     => $b->badge_name,
                    'description'    => $b->description,
                    'trust_threshold'=> $b->trust_threshold,
                    'awarded_at'     => $b->pivot->awarded_at ?? null,
                ])->values()
            ] : null,
            'parent_id'       => $comment->parent_id,
            'reply_to_user_id'=> $comment->parentComment && $comment->parentComment->user
                ? $comment->parentComment->user->user_id
                : null,
            'reply_to'        => $comment->parentComment && $comment->parentComment->user
                ? $comment->parentComment->user->username
                : null,
            'replies'         => $comment->replies && $comment->replies->count()
                ? $comment->replies->map(fn($r) => $this->formatComment($r))->toArray()
                : [],
        ];
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->trust_score < 10) {
            return response()->json([
                'message' => 'Your trust score is too low to comment (Minimum 10%).',
                'status' => 'error'
            ], 403);
        }
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
            $response = Http::timeout(2)->post('http://127.0.0.1:5000/analyze', ['text' => $request->content]);
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
            \Illuminate\Support\Facades\Log::warning("Sentiment AI Offline");

            \App\Models\TrustScoreLog::create([
                'user_id'      => auth()->id(),
                'action_type'  => 'system_warning',
                'score_change' => 0,
                'reason'       => 'Sentiment AI Offline - Post allowed without check',
            ]);
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

        $user = $comment->user;

        if ($isToxic) {
            \App\Models\Report::create([
                'reporter_id'     => null,
                'reportable_id'   => $comment->comment_id,
                'reportable_type' => Comment::class,
                'reason'          => 'High Negative Sentiment',
                'details'         => "AI detected toxic comment (" . round($confidence * 100, 1) . "%).",
                'status'          => 'pending',
            ]);

            $user->applyTrustChange(User::TRUST_SCORE_COMMENT_PENALTY, 'Toxic comment detected by AI', 'ai_moderation');
        } else {
            $user->applyTrustChange(User::TRUST_SCORE_COMMENT_REWARD, 'Comment posted successfully', 'comment_reward');
        }

        $user->updateBadges();

        $post = Post::find($request->post_id);

        if ($post && !$isToxic) {

            if ($request->parent_id) {
                $parentComment = Comment::find($request->parent_id);

                if ($parentComment && $parentComment->user_id !== auth()->id()) {
                    $parentComment->user->notify(new NewCommentNotification(auth()->user(), $post, $parentComment));
                }
            }
            elseif ($post->user_id !== auth()->id()) {
                $post->user->notify(new NewCommentNotification(auth()->user(), $post));
            }
        }

        $comment->load(['user', 'parentComment.user', 'replies']);

        $responsePayload = $this->formatComment($comment);

        return response()->json([
            'data' => $responsePayload,
            'is_flagged' => $isToxic,
            'message' => $isToxic ? 'Comment submitted but held for moderation.' : 'Comment posted successfully!'
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
            $comment->user->applyTrustChange(User::TRUST_SCORE_COMMENT_PENALTY, 'Comment Deleted');
            $comment->user->updateBadges();
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

    public function updateStatus(Request $request, Comment $comment)
    {
        $request->validate([
            'status' => ['required', Rule::in(['published', 'moderated'])],
        ]);

        $previousStatus = $comment->status;
        $comment->status = $request->status;
        $comment->save();

        if ($previousStatus !== $comment->status && $comment->user) {
            if ($comment->status === 'moderated') {
                $comment->user->applyTrustChange(User::TRUST_SCORE_COMMENT_PENALTY, 'Comment Flagged / Moderated', 'ai_moderation');
            } elseif ($previousStatus === 'moderated' && $comment->status === 'published') {
                $comment->user->applyTrustChange(User::TRUST_SCORE_COMMENT_REWARD, 'Comment Restored to Published', 'comment_reward');
            }

            $comment->user->updateBadges();
        }

        return response()->json(['message' => "Comment status updated to {$comment->status}."]);
    }
}
