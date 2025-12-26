<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Like;
use App\Notifications\PostLikedNotification;

class LikeController extends Controller
{
    public function toggleLike($postId)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $like = Like::where('post_id', $post->post_id)
                    ->where('user_id', $userId)
                    ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'post_id' => $post->post_id,
                'user_id' => $userId,
            ]);
            $liked = true;

            if ($post->user_id !== $userId) {
                $post->user->notify(new PostLikedNotification(Auth::user(), $post));
            }
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
