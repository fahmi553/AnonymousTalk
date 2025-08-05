<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($postId)
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,post_id',
            'user_id' => 'required|exists:users,user_id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create($validated);

        return response()->json($comment, 201);
    }
}
