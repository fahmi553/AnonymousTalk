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

        $postsQuery = $profileUser->posts();
        $posts = ($isOwner || !$profileUser->hide_all_posts)
            ? $postsQuery
                ->withCount(['comments', 'likes'])
                ->where('hidden_in_profile', false)
                ->latest()
                ->get()
            : collect();

        $comments = $isOwner
            ? $profileUser->comments()
                ->with('post:post_id,title')
                ->latest()
                ->get()
            : collect();

        // ✅ Always include total comment count, even if comments are hidden
        $commentCount = $profileUser->comments()->count();

        return response()->json([
            'user' => $profileUser,
            'is_owner' => $isOwner,
            'posts' => $posts,
            'comments' => $comments,
            'comment_count' => $commentCount, // 👈 Added line
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
