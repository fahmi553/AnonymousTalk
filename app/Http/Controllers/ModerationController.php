<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    private function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }


    public function moderateContent($type, $id, $action)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $model = null;
        $possibleMorphTypes = [];

        if ($type === 'post') {
            $model = Post::findOrFail($id);
            $possibleMorphTypes = [
                Post::class,
                'App\Models\Post',
                'posts'
            ];
        } elseif ($type === 'comment') {
            $model = Comment::findOrFail($id);
            $possibleMorphTypes = [
                Comment::class,
                'App\Models\Comment',
                'comments'
            ];
        } else {
            return response()->json(['error' => 'Invalid content type'], 400);
        }

        switch ($action) {
            case 'approve':
                $model->status = 'published';
                break;
            case 'hide':
                $model->status = 'moderated';
                break;
            case 'delete':
                $model->status = 'deleted';
                break;
            default:
                 return response()->json(['error' => 'Invalid action'], 400);
        }
        $model->save();

        $updatedCount = Report::where('reportable_id', $id)
            ->whereIn('reportable_type', $possibleMorphTypes)
            ->where('status', 'pending')
            ->update(['status' => 'resolved']);

        return response()->json([
            'message' => ucfirst($type) . " successfully {$action}d. ($updatedCount reports closed)",
            'status'  => $model->status
        ]);
    }

    public function approvePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'published';
        $post->save();

        return response()->json(['message' => 'Post approved successfully.', 'post' => $post]);
    }

    public function hidePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'moderated';
        $post->save();

        return response()->json(['message' => 'Post hidden successfully.', 'post' => $post]);
    }

    public function deletePost($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post = Post::findOrFail($id);
        $post->status = 'deleted';
        $post->save();

        return response()->json(['message' => 'Post marked as deleted.', 'post' => $post]);
    }

    public function approveComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'published';
        $comment->save();

        return response()->json(['message' => 'Comment approved successfully.', 'comment' => $comment]);
    }

    public function hideComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'moderated';
        $comment->save();

        return response()->json(['message' => 'Comment hidden successfully.', 'comment' => $comment]);
    }

    public function deleteComment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->status = 'deleted';
        $comment->save();

        return response()->json(['message' => 'Comment marked as deleted.', 'comment' => $comment]);
    }
}
