<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $possibleMorphTypes = [Post::class, 'App\Models\Post', 'posts'];
        } elseif ($type === 'comment') {
            $model = Comment::findOrFail($id);
            $possibleMorphTypes = [Comment::class, 'App\Models\Comment', 'comments'];
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

        Log::info("Moderation: {$action} on {$type} #{$id}. Closed {$updatedCount} reports.");

        return response()->json([
            'message' => ucfirst($type) . " successfully {$action}d.",
            'reports_closed' => $updatedCount
        ]);
    }
}
