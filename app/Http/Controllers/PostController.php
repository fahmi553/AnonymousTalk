<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use App\Models\PostSentimentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with([
            'comments' => fn($q) => $q->where('status', 'published')->orderBy('created_at', 'asc'),
            'comments.user',
            'user',
            'categoryModel',
        ])->withCount([
            'likes',
            'comments' => fn($q) => $q->where('status', 'published')
        ]);

        $query->where('status', 'published');

        $query->whereDoesntHave('user', function ($q) {
            $q->where('hide_all_posts', true);
        });

        $query->where('hidden_in_profile', false);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->input('filter') === 'liked' && auth()->check()) {
            $query->whereHas('likes', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        if ($request->input('filter') === 'trending') {
            $query->where('created_at', '>=', now()->subHours(72));

            $query->orderBy('comments_count', 'desc')
                ->orderBy('likes_count', 'desc');
        }
        else {
            if ($request->filled('sort')) {
                match($request->sort) {
                    'asc' => $query->orderBy('created_at', 'asc'),
                    'most_commented' => $query->orderBy('comments_count', 'desc'),
                    default => $query->orderBy('created_at', 'desc')
                };
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(fn($q) => $q->where('title', 'like', "%{$searchTerm}%")
                                     ->orWhere('content', 'like', "%{$searchTerm}%"));
        }

        if ($request->filled('sort')) {
            match($request->sort) {
                'asc' => $query->orderBy('created_at', 'asc'),
                'most_commented' => $query->orderBy('comments_count', 'desc')->orderBy('created_at', 'desc'),
                default => $query->orderBy('created_at', 'desc')
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->boolean('with_comments_only') && $request->sort !== 'most_commented') {
            $query->has('comments');
        }

        $perPage = $request->input('per_page', 10);
        $postsPaginated = $query->paginate($perPage);

        $likedPostIds = auth()->check()
            ? Like::whereIn('post_id', $postsPaginated->pluck('post_id')->toArray())
                  ->where('user_id', auth()->id())
                  ->pluck('post_id')
                  ->toArray()
            : [];

        $postsData = $postsPaginated->getCollection()->map(fn($post) => [
            'post_id'       => $post->post_id,
            'title'         => $post->title,
            'content'       => $post->content,
            'status'        => $post->status,
            'created_at'    => $post->created_at->toISOString(),
            'user'          => [
                'username' => $post->user->username ?? 'Anonymous',
                'avatar'   => $post->user->avatar ?? 'default.jpg',
            ],
            'category'      => $post->categoryModel?->name,
            'comments'      => $post->comments->map(fn($c) => $this->formatComment($c)),
            'likes_count'   => $post->likes_count,
            'comments_count'=> $post->comments_count,
            'liked'         => in_array($post->post_id, $likedPostIds),
        ]);

        return response()->json([
            'data' => $postsData,
            'meta' => [
                'current_page' => $postsPaginated->currentPage(),
                'last_page'    => $postsPaginated->lastPage(),
                'per_page'     => $postsPaginated->perPage(),
                'total'        => $postsPaginated->total(),
            ],
        ]);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->trust_score < 10) {
            return response()->json([
                'message' => 'Your trust score is too low to create posts (Minimum 10%).',
                'status' => 'error'
            ], 403);
        }

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,category_id',
        ]);

        $textToAnalyze = $request->title ? $request->title . ". " . $request->content : $request->content;
        $sentimentLabel = 'POSITIVE';
        $confidence = 0.0;
        $isToxic = false;
        $status = 'published';

        try {
            $response = Http::timeout(2)->post('http://127.0.0.1:5000/analyze', ['text' => $textToAnalyze]);
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
            \Illuminate\Support\Facades\Log::warning("Sentiment AI Offline: " . $e->getMessage());
        }

        if (!$isToxic && $user->trust_score < 30) {
        $status = 'pending';
        }

        $post = \App\Models\Post::create([
            'user_id'         => auth()->id(),
            'category_id'     => $request->category_id,
            'title'           => $request->title,
            'content'         => $request->content,
            'sentiment_score' => $confidence,
            'status'          => $status,
        ]);

        \App\Models\PostSentimentLog::create([
            'post_id'         => $post->post_id,
            'sentiment_score' => $confidence,
            'result'          => $sentimentLabel,
            'created_at'      => now(),
        ]);

        if ($isToxic) {
            \App\Models\Report::create([
                'reporter_id'     => null,
                'reportable_id'   => $post->post_id,
                'reportable_type' => \App\Models\Post::class,
                'reason'          => 'High Negative Sentiment',
                'details'         => "AI detected toxic post content (" . round($confidence * 100, 1) . "%).",
                'status'          => 'pending',
            ]);

            $user->applyTrustChange(
                -2,
                'Toxic Post Flagged',
                'ai_moderation'
            );

        } elseif ($status === 'published') {
            $user->applyTrustChange(2, 'Post Submitted', 'post_reward');
        }

        return response()->json([
        'message' => $isToxic
                ? 'Post flagged as toxic. -2 Trust Score applied.'
                : ($status === 'pending' ? 'Post submitted for approval (Trust Score < 30%).' : 'Post created successfully!'),
        'status'  => ($isToxic || $status === 'pending') ? 'warning' : 'success',
        'post'    => $post
        ]);
    }

    public function destroy($id)
    {
        $post = Post::with(['comments.user', 'comments.replies.user'])->findOrFail($id);

        if (auth()->id() !== $post->user_id && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($post) {
            foreach ($post->comments as $comment) {
                $this->deleteCommentRecursively($comment, $post->user_id);
            }
            if ($post->user) {
                $post->user->applyTrustChange(User::TRUST_SCORE_POST_PENALTY, 'Post Deleted', 'post_penalty');
                $post->user->updateBadges();
            }

            $post->delete();
        });

        return response()->json(['message' => 'Post deleted successfully']);
    }

    private function deleteCommentRecursively($comment, $targetUserId)
    {
        $comment->load('user', 'replies.user');

        foreach ($comment->replies as $reply) {
            $this->deleteCommentRecursively($reply, $targetUserId);
        }

        if ($comment->user_id === $targetUserId && $comment->user) {
            $comment->user->applyTrustChange(User::TRUST_SCORE_COMMENT_PENALTY, 'Own Comment Deleted with Post', 'comment_penalty');
            $comment->user->updateBadges();
        }

        $comment->delete();
    }
    public function updateStatus(Request $request, Post $post)
    {
        $request->validate(['status' => ['required', Rule::in(Post::STATUSES)]]);
        $previousStatus = $post->status;
        $post->status = $request->status;
        $post->save();

        if ($previousStatus !== $post->status && $post->user) {
            if ($post->status === 'moderated') {
                $post->user->applyTrustChange(User::TRUST_SCORE_POST_PENALTY, 'Post Flagged / Moderated', 'ai_moderation');
            } elseif ($previousStatus === 'moderated' && $post->status === 'published') {
                $post->user->applyTrustChange(User::TRUST_SCORE_POST_REWARD, 'Post Restored to Published', 'post_reward');
            }

            $post->user->updateBadges();
        }

        return response()->json(['message' => "Post status updated to {$post->status}."]);
    }

    public function show($id)
    {
        $post = Post::with([
            'user:id,username',
            'comments.user:id,username',
            'comments.replies.user:id,username'
        ])->find($id);

        return $post
            ? response()->json($post)
            : response()->json(['error' => 'Post not found'], 404);
    }

    public function showApi($postId)
    {
        $post = Post::with([
            'user',
            'categoryModel',
            'comments' => fn($q) => $q->whereNull('parent_id')
                ->whereDoesntHave('reports', fn($subQ) => $subQ->whereNull('reporter_id')->where('status', 'pending'))
                ->with(['user', 'parentComment.user', 'replies.user', 'replies.parentComment.user', 'replies.replies.user']),
        ])->withCount(['likes', 'comments'])->findOrFail($postId);

        $liked = auth()->check()
            ? Like::where('post_id', $post->post_id)->where('user_id', auth()->id())->exists()
            : false;

        return response()->json([
            'post_id'     => $post->post_id,
            'title'       => $post->title,
            'content'     => $post->content,
            'category'    => $post->categoryModel->name ?? null,
            'created_at'  => $post->created_at?->toISOString(),
            'user'        => [
                'user_id'  => $post->user->user_id ?? null,
                'username' => $post->user->username ?? 'Anonymous',
                'avatar'   => $post->user->avatar ?? 'default.jpg'
            ],
            'likes_count' => $post->likes_count ?? $post->likes()->count(),
            'liked'       => $liked,
            'comments'    => $post->comments->map(fn($c) => $this->formatComment($c))->values(),
        ]);
    }

    protected function formatComment($comment)
    {
        return [
            'comment_id' => $comment->comment_id,
            'content'    => $comment->content,
            'created_at' => $comment->created_at?->toISOString(),
            'user'       => [
                'user_id'  => $comment->user->user_id ?? null,
                'username' => $comment->user->username ?? 'Anonymous',
                'avatar'   => $comment->user->avatar ?? 'default.jpg',
            ],
            'parent_id'       => $comment->parent_id,
            'reply_to_user_id'=> $comment->parentComment?->user?->user_id,
            'reply_to'        => $comment->parent_id ? optional($comment->parentComment->user)->username : null,
            'replies'         => $comment->replies ? $comment->replies->map(fn($r) => $this->formatComment($r))->values() : [],
        ];
    }

    public function getTrendingPosts()
    {
        $trendingPosts = Post::with([
                'user:user_id,username,avatar',
                'categoryModel:category_id,name'
            ])
            ->withCount(['likes', 'comments'])

            ->where('status', 'published')

            ->where('created_at', '>=', now()->subHours(72))
            ->orderBy('comments_count', 'desc')
            ->orderBy('likes_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json($trendingPosts->map(fn($post) => [
            'post_id' => $post->post_id,
            'title' => $post->title,
            'user' => [
                'username' => $post->user->username ?? 'Anonymous',
                'avatar'   => $post->user->avatar ?? 'default.jpg'
            ],
            'comments_count' => $post->comments_count,
            'likes_count' => $post->likes_count,
        ]));
    }
}
