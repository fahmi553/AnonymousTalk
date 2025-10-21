<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use App\Helpers\UsernameGenerator;
use App\Models\Post;
use App\Models\Comment;

class ProfileController extends Controller
{
    public function show(Request $request, $id = null)
    {
        if ($id) {
            $profileUser = \App\Models\User::with(['badges'])->findOrFail($id);
        } else {
            $profileUser = $request->user();
            if (!$profileUser) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
        }

        $authUser = auth()->user();
        $isOwner = $authUser && $authUser->user_id === $profileUser->user_id;

        $postsQuery = $profileUser->posts();
        if (!$isOwner && $profileUser->hide_all_posts) {
            $posts = collect();
        } else {
            $posts = $postsQuery->where('hidden_in_profile', false)
                ->latest()
                ->get();
        }

        $commentsQuery = $profileUser->comments();
        if (!$isOwner && $profileUser->hide_all_comments) {
            $comments = collect();
        } else {
            $comments = $commentsQuery->where('hidden_in_profile', false)
                ->latest()
                ->get();
        }

        return response()->json([
            'user' => $profileUser,
            'is_owner' => $isOwner,
            'posts' => $posts,
            'comments' => $comments,
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
            'email'    => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->username = $request->username;
        $user->email    = $request->email;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        if ($user->isDirty('username')) {
            $user->last_username_change = now();
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user'    => $user,
        ]);
    }
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function regenerateUsername(Request $request)
    {
        $user = $request->user();

        $user->username = UsernameGenerator::generate();
        $user->save();

        return response()->json([
            'message'  => 'Username regenerated successfully',
            'username' => $user->username
        ]);
    }

    public function userPosts($id)
    {
        $query = \App\Models\Post::where('user_id', $id);

        if (!Auth::check() || Auth::id() != $id) {
            $query->where('hidden_in_profile', false);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function userComments($id)
    {
        $query = \App\Models\Comment::where('user_id', $id)
            ->with('post:post_id,title');

        if (!Auth::check() || Auth::id() != $id) {
            $query->where('hidden_in_profile', false);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function togglePostVisibility($id)
    {
        $post = \App\Models\Post::findOrFail($id);

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

    public function toggleCommentVisibility($id)
    {
        $comment = \App\Models\Comment::findOrFail($id);

        if (Auth::id() !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->hidden_in_profile = !$comment->hidden_in_profile;
        $comment->save();

        return response()->json([
            'message' => 'Comment visibility updated.',
            'hidden_in_profile' => $comment->hidden_in_profile,
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
                : 'All posts are now visible on your profile.'
        ]);
    }

    public function toggleHideAllComments()
    {
        $user = auth()->user();
        $user->hide_all_comments = !$user->hide_all_comments;
        $user->save();

        return response()->json([
            'success' => true,
            'hide_all_comments' => $user->hide_all_comments,
            'message' => $user->hide_all_comments
                ? 'All comments are now hidden from your profile.'
                : 'All comments are now visible on your profile.'
        ]);
    }
}
