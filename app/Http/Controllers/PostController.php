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
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'user_id'     => 'required|exists:users,user_id',
        ]);

        $post = Post::create($validated);

        return response()->json([
            'post_id'    => $post->post_id,
            'title'      => $post->title,
            'content'    => $post->content,
            'created_at' => $post->created_at->toISOString(),
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

    public function show($id)
    {
        $post = Post::with([
            'user:id,username',
            'comments.user:id,username',
            'comments.replies.user:id,username'
        ])->find($id);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json($post);
    }

    protected function formatComment($comment)
    {
        return [
            'comment_id' => $comment->comment_id,
            'content'    => $comment->content,
            'created_at' => $comment->created_at ? $comment->created_at->toISOString() : null,
            'user'       => [
                'user_id'  => $comment->user->user_id ?? null,
                'username' => $comment->user->username ?? 'Anonymous',
            ],
            'parent_id'  => $comment->parent_id,
            'reply_to_user_id' => $comment->parentComment?->user?->user_id,
            'reply_to'   => $comment->parent_id
                            ? optional($comment->parentComment->user)->username
                            : null,
            'replies'    => $comment->replies
                                    ? $comment->replies->map(fn($reply) => $this->formatComment($reply))->values()
                                    : [],
        ];
    }

    public function showApi($postId)
    {
        $post = Post::with([
                'user',
                'categoryModel',
                'comments' => fn($q) => $q->whereNull('parent_id')->with([
                    'user',
                    'parentComment.user',
                    'replies.user',
                    'replies.parentComment.user',
                    'replies.replies.user',
                ]),
            ])
            ->withCount(['likes', 'comments'])
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
            'created_at'  => $post->created_at?->toISOString(),
            'user'        => [
                'user_id'  => $post->user->user_id ?? null,
                'username' => $post->user->username ?? 'Anonymous',
            ],
            'likes_count' => $post->likes_count,
            'liked'       => $liked,
            'comments'    => $post->comments->map(fn($c) => $this->formatComment($c))->values(),
        ]);
    }

    private function transformComment($comment)
    {
        return [
            'comment_id' => $comment->comment_id,
            'content'    => $comment->content,
            'created_at' => optional($comment->created_at)->toISOString(),
            'user'       => [
                'username' => $comment->user->username ?? 'Anonymous',
            ],
            'parent_id'  => $comment->parent_id,
            'reply_to'   => $comment->parentComment
                ? optional($comment->parentComment->user)->username
                : null,
            'replies'    => $comment->replies
                ? $comment->replies->map(fn($r) => $this->transformComment($r))
                : [],
        ];
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
