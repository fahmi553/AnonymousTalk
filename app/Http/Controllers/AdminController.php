<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class AdminController extends Controller
{
    public function allPosts()
    {
        $this->authorizeAdmin();
        return response()->json(Post::with('user')->latest()->get());
    }

    public function allComments()
    {
        $this->authorizeAdmin();
        return response()->json(Comment::with(['user', 'post'])->latest()->get());
    }

    public function togglePostStatus($id)
    {
        $this->authorizeAdmin();
        $post = Post::findOrFail($id);

        $post->status = $post->status === 'visible' ? 'hidden' : 'visible';
        $post->save();

        return response()->json(['message' => "Post status changed to {$post->status}."]);
    }

    public function toggleCommentStatus($id)
    {
        $this->authorizeAdmin();
        $comment = Comment::findOrFail($id);

        $comment->status = $comment->status === 'visible' ? 'hidden' : 'visible';
        $comment->save();

        return response()->json(['message' => "Comment status changed to {$comment->status}."]);
    }

    public function deletePost($id)
    {
        $this->authorizeAdmin();
        Post::findOrFail($id)->delete();
        return response()->json(['message' => 'Post deleted successfully.']);
    }

    public function deleteComment($id)
    {
        $this->authorizeAdmin();
        Comment::findOrFail($id)->delete();
        return response()->json(['message' => 'Comment deleted successfully.']);
    }

    private function authorizeAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
