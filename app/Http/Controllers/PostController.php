<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['comments' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }, 'comments.user']);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->get()->map(function ($post) {
            return [
                'post_id' => $post->post_id,
                'title' => $post->title,
                'content' => $post->content,
                'category' => $post->category,
                'created_at' => $post->created_at->toISOString(),
                'user' => [
                    'username' => $post->user->username ?? 'Anonymous'
                ],
                'comments' => $post->comments->map(function ($comment) {
                    return [
                        'comment_id' => $comment->comment_id,
                        'content' => $comment->content,
                        'user' => [
                            'username' => $comment->user->username ?? 'Anonymous'
                        ]
                    ];
                })
            ];
        });

        return response()->json($posts);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category ?? 'General'
        ]);

        $post->load('user');

        return response()->json($post);
    }

    public function updateStatus(Request $request, Post $post)
    {
        $request->validate([
            'status' => ['required', Rule::in(Post::STATUSES)],
        ]);

        $post->status = $request->status;
        $post->save();

        return response()->json(['message' => 'Post status updated.']);
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function showApi(Post $post)
    {
        $post->load([
            'user',
            'comments' => function ($query) {
                $query->orderBy('created_at', 'asc')
                    ->with('user');
            }
        ]);

        return response()->json($post);
    }

}
