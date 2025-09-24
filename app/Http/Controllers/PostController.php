<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with([
            'comments' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'comments.user',
            'user',
            'categoryModel',
        ])->withCount(['likes', 'comments']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'asc') {
                $query->orderBy('created_at', 'asc');
            } elseif ($request->sort === 'most_commented') {
                $query->orderBy('comments_count', 'desc')
                    ->orderBy('created_at', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (
            $request->boolean('with_comments_only') &&
            $request->sort !== 'most_commented'
        ) {
            $query->has('comments');
        }

        $postsCollection = $query->get();

        $likedPostIds = [];
        if (auth()->check()) {
            $likedPostIds = Like::whereIn(
                    'post_id',
                    $postsCollection->pluck('post_id')->toArray()
                )
                ->where('user_id', auth()->id())
                ->pluck('post_id')
                ->toArray();
        }

        $posts = $postsCollection->map(function ($post) use ($likedPostIds) {
            return [
                'post_id'    => $post->post_id,
                'title'      => $post->title,
                'content'    => $post->content,
                'status'     => $post->status,
                'created_at' => $post->created_at->toISOString(),
                'user'       => [
                    'username' => $post->user->username ?? 'Anonymous'
                ],
                'category'   => $post->categoryModel?->name,
                'comments'   => $post->comments->map(function ($comment) {
                    return [
                        'comment_id' => $comment->comment_id,
                        'content'    => $comment->content,
                        'created_at' => $comment->created_at->toISOString(),
                        'user'       => [
                            'username' => $comment->user->username ?? 'Anonymous'
                        ]
                    ];
                }),
                'likes_count' => $post->likes_count,
                'liked'       => in_array($post->post_id, $likedPostIds),
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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|exists:categories,name',
        ]);

        $post = Post::create($validated);
        $post->load(['user', 'categoryModel', 'comments.user']);

        return response()->json([
            'post_id'   => $post->post_id,
            'title'     => $post->title,
            'content'   => $post->content,
            'status'    => $post->status,
            'created_at'=> $post->created_at->toISOString(),
            'user'      => [
                'username' => $post->user->username ?? 'Anonymous',
            ],
            'category'  => $post->categoryModel?->name,
            'comments'  => $post->comments->map(fn ($comment) => [
                'comment_id' => $comment->comment_id,
                'content'    => $comment->content,
                'created_at' => $comment->created_at->toISOString(),
                'user'       => [
                    'username' => $comment->user->username ?? 'Anonymous',
                ],
            ]),
        ], 201);
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

    public function showApi($postId)
    {
        $post = Post::with([
            'user',
            'comments.user',
            'categoryModel',
        ])->withCount(['likes', 'comments'])
        ->findOrFail($postId);

        $liked = false;
        if (auth()->check()) {
            $liked = \App\Models\Like::where('post_id', $post->post_id)
                ->where('user_id', auth()->id())
                ->exists();
        }

        return response()->json([
            'post_id'     => $post->post_id,
            'title'       => $post->title,
            'content'     => $post->content,
            'category'    => $post->categoryModel->name ?? null,
            'created_at'  => $post->created_at ? $post->created_at->toISOString() : null,
            'user'        => [
                'username' => $post->user->username ?? 'Anonymous',
            ],
            'comments'    => $post->comments->sortBy('created_at')->map(function ($comment) {
                return [
                    'comment_id' => $comment->comment_id,
                    'content'    => $comment->content,
                    'created_at' => $comment->created_at ? $comment->created_at->toISOString() : null,
                    'user'       => [
                        'username' => $comment->user->username ?? 'Anonymous',
                    ],
                ];
            })->values(),
            'likes_count' => $post->likes_count,
            'liked'       => $liked,
        ]);
    }

    public function toggleLike(Post $post)
    {
        $userId = auth()->id();
        $existing = Like::where('post_id', $post->post_id)
                        ->where('user_id', $userId)
                        ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'post_id' => $post->post_id,
                'user_id' => $userId,
            ]);
            $liked = true;
        }
        $likesCount = $post->likes()->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }

}
