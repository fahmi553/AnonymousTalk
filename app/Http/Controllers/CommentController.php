<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($postId)
    {
        $perPage = request()->get('per_page', 5);

        $comments = Comment::with('user')
            ->where('post_id', $postId)
            ->oldest()
            ->paginate($perPage);

        return response()->json($comments);
    }

    private function formatComment($comment)
    {
        return [
            'comment_id' => $comment->comment_id,
            'content'    => $comment->content,
            'created_at' => $comment->created_at,
            'user'       => [
                'user_id'  => $comment->user->user_id,
                'username' => $comment->user->username,
            ],
            'replies'    => $comment->replies->map(fn($reply) => $this->formatComment($reply))->toArray(),
            'reply_to'   => $comment->parentComment
                ? [
                    'comment_id' => $comment->parentComment->comment_id,
                    'user'       => [
                        'user_id'  => $comment->parentComment->user->user_id,
                        'username' => $comment->parentComment->user->username,
                    ],
                ]
                : null,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id'   => 'required|exists:posts,post_id',
            'content'   => 'required|string',
            'parent_id' => 'nullable|exists:comments,comment_id',
        ]);

        $comment = \App\Models\Comment::create([
            'post_id'   => $request->post_id,
            'user_id'   => auth()->id(),
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        $comment->load(['user', 'parentComment.user', 'replies']);

        return response()->json($this->formatComment($comment));
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if (auth()->id() !== $comment->user_id && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'message'    => 'Comment deleted successfully',
            'comment_id' => $id
        ]);
    }

    private function deleteWithReplies($comment)
    {
        foreach ($comment->replies as $reply) {
            $this->deleteWithReplies($reply);
        }
        $comment->delete();
    }
}
