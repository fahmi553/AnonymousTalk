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
    /**
     * Display the specified resource.
     *
     * This function is refactored to handle different data requests
     * based on query parameters, matching the new Vue component.
     */
    public function show(Request $request, $id = null)
    {
        // 1. Get User and Ownership
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

        // 2. Handle 'user_only' request (for initial page load)
        if ($request->boolean('user_only')) {
            // Get aggregate counts for the stats bar
            $postCount = $profileUser->posts()->count();
            $commentCount = $profileUser->comments()->count();

            return response()->json([
                'user' => $profileUser,
                'is_owner' => $isOwner,
                'comment_count' => $commentCount, // Total comments for stats
                'posts_meta' => ['total' => $postCount], // Total posts for stats
                'comments_meta' => ['total' => $commentCount] // Total comments for stats
            ]);
        }

        // 3. Handle data tab requests (for pagination)
        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search', null);

        // --- Handle POSTS Tab ---
        if ($request->input('tab') === 'posts') {
            $postsQuery = $profileUser->posts()
                ->withCount(['comments', 'likes'])
                ->latest();

            // Apply visibility rules
            if (!$isOwner) {
                // If not owner, check if profile is private
                if ($profileUser->hide_all_posts) {
                    // Return empty if private and not owner
                    return response()->json([
                        'posts' => [],
                        'posts_meta' => ['total' => 0, 'current_page' => 1, 'last_page' => 1, 'per_page' => $perPage]
                    ]);
                }
                // If not owner and not private, only show non-hidden posts
                $postsQuery->where('hidden_in_profile', false);
            }
            // If owner, they see all their posts (no filter needed, frontend shows 'hidden' status)

            if (!empty($search) && $isOwner) { // Only owner can search
                $postsQuery->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $postsPaginated = $postsQuery->paginate($perPage);

            return response()->json([
                'posts' => $postsPaginated->items(),
                'posts_meta' => [
                    'current_page' => $postsPaginated->currentPage(),
                    'last_page'    => $postsPaginated->lastPage(),
                    'per_page'     => $postsPaginated->perPage(),
                    'total'        => $postsPaginated->total(),
                ]
            ]);
        }

        // --- Handle COMMENTS Tab ---
        if ($request->input('tab') === 'comments' && $isOwner) {
            $commentsQuery = $profileUser->comments()
                ->with('post:post_id,title')
                ->latest();

            if (!empty($search)) {
                $commentsQuery->where('content', 'like', "%{$search}%");
            }

            $commentsPaginated = $commentsQuery->paginate($perPage);

            return response()->json([
                'comments' => $commentsPaginated->items(),
                'comments_meta' => [
                    'current_page' => $commentsPaginated->currentPage(),
                    'last_page'    => $commentsPaginated->lastPage(),
                    'per_page'     => $commentsPaginated->perPage(),
                    'total'        => $commentsPaginated->total(),
                ]
            ]);
        }

        // Fallback for any other request (or if tab=comments and not owner)
        return response()->json(['error' => 'Invalid request or unauthorized tab.'], 400);
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
