<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\User;

class CommentController extends Controller
{
    public function index($postId)
    {
        $comments = Comment::with([
                'user',
                'parentComment.user',
                'replies.user',
                'replies.parentComment.user',
                'replies.replies'
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
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
            'content'   => 'required|string',
            'parent_id' => 'nullable|exists:comments,comment_id',
        ]);

        $comment = Comment::create([
            'post_id'   => $request->post_id,
            'user_id'   => auth()->id(),
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        $comment->user->updateTrustScore(User::TRUST_SCORE_COMMENT_REWARD);

        $comment->load(['user', 'parentComment.user', 'replies']);

        return response()->json($this->formatComment($comment));
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
            $comment->user->updateTrustScore(User::TRUST_SCORE_COMMENT_PENALTY);
        }

        $comment->delete();
    }
}
