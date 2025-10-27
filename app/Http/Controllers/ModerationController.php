<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    private function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public function approvePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'published';
        $post->save();

        return response()->json(['message' => 'Post approved successfully.', 'post' => $post]);
    }

    public function hidePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'moderated';
        $post->save();

        return response()->json(['message' => 'Post hidden successfully.', 'post' => $post]);
    }

    public function deletePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'deleted';
        $post->save();

        return response()->json(['message' => 'Post marked as deleted.', 'post' => $post]);
    }

    public function approveComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'published';
        $comment->save();

        return response()->json(['message' => 'Comment approved successfully.', 'comment' => $comment]);
    }

    public function hideComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'moderated';
        $comment->save();

        return response()->json(['message' => 'Comment hidden successfully.', 'comment' => $comment]);
    }

    public function deleteComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'deleted';
        $comment->save();

        return response()->json(['message' => 'Comment marked as deleted.', 'comment' => $comment]);
    }
}
