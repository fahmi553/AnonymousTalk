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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,post_id',
            'user_id' => 'required|exists:users,user_id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create($validated);
        $comment->load('user');

        return response()->json($comment, 201);
    }
}
