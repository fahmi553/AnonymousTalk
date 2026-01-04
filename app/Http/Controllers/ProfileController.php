<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\UsernameGenerator;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;
use App\Models\Like;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{

    private $allowedAvatars = [
        'default.jpg',
        'avatar1.jpg',
        'avatar2.jpg',
        'avatar3.jpg',
        'avatar4.jpg',
        'avatar5.jpg',
        'avatar6.jpg',
        'avatar7.jpg',
        'avatar8.jpg',
        'avatar9.jpg',
    ];

    public function show(Request $request, $id = null)
    {
        $profileUser = $id
            ? User::with('badges')->findOrFail($id)
            : $request->user();

        if (!$profileUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $authUser = auth()->user();
        $isOwner = $authUser && $authUser->user_id === $profileUser->user_id;

        $allBadges = Badge::all();
        $profileUserArray = $profileUser->toArray();
        $profileUserArray['is_google_user'] = !empty($profileUser->google_id);
        $profileUserArray['is_verified'] = !is_null($profileUser->email_verified_at);

        $trustBadges = $allBadges
            ->whereNotNull('trust_threshold')
            ->sortByDesc('trust_threshold');

        $highestTrustBadge = $trustBadges->first(
            fn ($b) => $profileUser->trust_score >= $b->trust_threshold
        );
        $profileUserArray['badges'] = $allBadges
            ->map(function ($badge) use ($profileUser, $highestTrustBadge) {

                $earnedBadge = $profileUser->badges
                    ->firstWhere('badge_id', $badge->badge_id);

                if ($badge->trust_threshold !== null) {

                    if (
                        $highestTrustBadge &&
                        $badge->trust_threshold < $highestTrustBadge->trust_threshold
                    ) {
                        return null;
                    }

                    return [
                        'badge_id' => $badge->badge_id,
                        'badge_name' => $badge->badge_name,
                        'description' => $badge->description,
                        'trust_threshold' => $badge->trust_threshold,
                        'icon_url' => $badge->icon_url,
                        'awarded_at' => $badge->trust_threshold <= $profileUser->trust_score
                            ? now()
                            : null,
                        'reason' => "Earned for reaching trust score ≥ {$badge->trust_threshold}",
                        'locked' => !$highestTrustBadge
                            || $badge->trust_threshold > $highestTrustBadge->trust_threshold,
                        'badge_type' => $badge->badge_type,
                    ];
                }
                if ($badge->badge_type === 'status') {
                    if (!$earnedBadge) {
                        return null;
                    }
                }
                return [
                    'badge_id' => $badge->badge_id,
                    'badge_name' => $badge->badge_name,
                    'description' => $badge->description,
                    'trust_threshold' => null,
                    'icon_url' => $badge->icon_url,
                    'awarded_at' => $earnedBadge?->pivot?->awarded_at,
                    'reason' => 'Earned for behavior',
                    'locked' => $earnedBadge === null,
                    'badge_type' => $badge->badge_type,
                ];
            })
            ->filter()
            ->sortBy(fn ($b) => $b['locked'])
            ->values()
            ->toArray();

        if ($request->boolean('user_only')) {
            $postCount = $profileUser->posts()->where('status', 'published')->count();
            $commentCount = $profileUser->comments()->where('status', 'published')->count();

            return response()->json([
                'user' => $profileUserArray,
                'is_owner' => $isOwner,
                'comment_count' => $commentCount,
                'posts_meta' => ['total' => $postCount],
                'comments_meta' => ['total' => $commentCount],
            ]);
        }

        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search');

        if ($request->input('tab') === 'posts') {
            $postsQuery = $profileUser->posts()
                ->withCount(['comments', 'likes'])
                ->with('user:user_id,username,avatar')
                ->where('status', 'published')
                ->latest();

            if (!$isOwner) {
                if ($profileUser->hide_all_posts) {
                    return response()->json([
                        'posts' => [],
                        'posts_meta' => [
                            'total' => 0,
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => $perPage
                        ],
                    ]);
                }

                $postsQuery->where('hidden_in_profile', false);
            }

            if ($search && $isOwner) {
                $postsQuery->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $postsPaginated = $postsQuery->paginate($perPage);

            return response()->json([
                'user' => $profileUserArray,
                'is_owner' => $isOwner,
                'posts' => $postsPaginated->items(),
                'posts_meta' => [
                    'current_page' => $postsPaginated->currentPage(),
                    'last_page' => $postsPaginated->lastPage(),
                    'per_page' => $postsPaginated->perPage(),
                    'total' => $postsPaginated->total(),
                ],
            ]);
        }

        if ($request->input('tab') === 'comments' && $isOwner) {
            $commentsQuery = $profileUser->comments()
                ->with('post:post_id,title')
                ->where('status', 'published')
                ->latest();

            if ($search) {
                $commentsQuery->where('content', 'like', "%{$search}%");
            }

            $commentsPaginated = $commentsQuery->paginate($perPage);

            return response()->json([
                'user' => $profileUserArray,
                'is_owner' => $isOwner,
                'comments' => $commentsPaginated->items(),
                'comments_meta' => [
                    'current_page' => $commentsPaginated->currentPage(),
                    'last_page' => $commentsPaginated->lastPage(),
                    'per_page' => $commentsPaginated->perPage(),
                    'total' => $commentsPaginated->total(),
                ],
            ]);
        }

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
            'avatar' => 'nullable|string|in:' . implode(',', $this->allowedAvatars),
        ]);

        $user->username = $request->username;
        $user->email = $request->email;

        if ($request->has('avatar')) {
            $user->avatar = $request->avatar;
        }

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

    public function getAvatars()
    {
        return response()->json([
            'avatars' => $this->allowedAvatars,
            'path' => asset('images/avatars/') . '/'
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        $isGoogleUser = !empty($user->google_id);

        if (!$isGoogleUser) {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->posts()->delete();
                $user->comments()->delete();

                DB::table('likes')->where('user_id', $user->user_id)->delete();

                DB::table('notifications')->where('notifiable_id', $user->user_id)->delete();

                $user->trustScoreLogs()->delete();
                $user->badges()->detach();
                $user->reportsFiled()->delete();
                $user->delete();
                $user->tokens()->delete();
            });

            return response()->json(['message' => 'Account deleted successfully.']);

        } catch (\Exception $e) {
            Log::error('Delete Account Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Database Error: ' . $e->getMessage()], 500);
        }
    }
}
