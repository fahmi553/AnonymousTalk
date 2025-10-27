<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\UsernameGenerator;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class ProfileController extends Controller
{
    public function show(Request $request, $id = null)
    {
        if ($id) {
            $profileUser = User::with('badges')->findOrFail($id);
        } else {
            $profileUser = $request->user();
            if (!$profileUser) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
        }

        $authUser = auth()->user();
        $isOwner = $authUser && $authUser->user_id === $profileUser->user_id;

        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search', null);

        $postsQuery = $profileUser->posts()
            ->withCount(['comments', 'likes'])
            ->where('hidden_in_profile', false)
            ->latest();

        if (!empty($search)) {
            $postsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = [];
        $postsMeta = null;

        if ($isOwner || !$profileUser->hide_all_posts) {
            $postsPaginated = $postsQuery->paginate($perPage);

            $posts = $postsPaginated->items();

            $postsMeta = [
                'current_page' => $postsPaginated->currentPage(),
                'last_page'    => $postsPaginated->lastPage(),
                'per_page'     => $postsPaginated->perPage(),
                'total'        => $postsPaginated->total(),
            ];
        }
        $comments = [];
        $commentsMeta = null;

        if ($isOwner) {
            $commentsQuery = $profileUser->comments()
                ->with('post:post_id,title')
                ->latest();

            if (!empty($search)) {
                $commentsQuery->where('content', 'like', "%{$search}%");
            }

            $commentsPaginated = $commentsQuery->paginate($perPage);
            $comments = $commentsPaginated->items();
            $commentsMeta = [
                'current_page' => $commentsPaginated->currentPage(),
                'last_page'    => $commentsPaginated->lastPage(),
                'per_page'     => $commentsPaginated->perPage(),
                'total'        => $commentsPaginated->total(),
            ];
        }

        $commentCount = $profileUser->comments()->count();

        return response()->json([
            'user' => $profileUser,
            'is_owner' => $isOwner,
            'posts' => $posts,
            'posts_meta' => $postsMeta,
            'comments' => $comments,
            'comments_meta' => $commentsMeta,
            'comment_count' => $commentCount,
        ]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->username = $request->username;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->isDirty('username')) {
            $user->last_username_change = now();
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user,
        ]);
    }

    public function regenerateUsername(Request $request)
    {
        $user = $request->user();

        $user->username = UsernameGenerator::generate();
        $user->save();

        return response()->json([
            'message' => 'Username regenerated successfully',
            'username' => $user->username,
        ]);
    }

    public function togglePostVisibility($id)
    {
        $post = Post::findOrFail($id);

        if (Auth::id() !== $post->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->hidden_in_profile = !$post->hidden_in_profile;
        $post->save();

        return response()->json([
            'message' => 'Post visibility updated.',
            'hidden_in_profile' => $post->hidden_in_profile,
        ]);
    }

    public function toggleHideAllPosts()
    {
        $user = auth()->user();
        $user->hide_all_posts = !$user->hide_all_posts;
        $user->save();

        return response()->json([
            'success' => true,
            'hide_all_posts' => $user->hide_all_posts,
            'message' => $user->hide_all_posts
                ? 'All posts are now hidden from your profile.'
                : 'All posts are now visible on your profile.',
        ]);
    }
}
