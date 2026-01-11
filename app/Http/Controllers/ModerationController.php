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

    public function moderateContent(Request $request, $type, $id, $action)
    {
        $modelClass = $type === 'post' ? \App\Models\Post::class : \App\Models\Comment::class;
        $content = $modelClass::findOrFail($id);

        $previousStatus = $content->status;

        switch ($action) {
            case 'approve':
                $content->status = 'published';
                $content->save();
                if ($previousStatus !== 'published') {

                    if ($type === 'post' && $content->user) {
                        $content->user->applyTrustChange(2, 'Post Approved by Admin', 'post_approved');
                    }
                    elseif ($type === 'comment' && $content->user) {
                         $content->user->applyTrustChange(1, 'Comment Approved by Admin', 'comment_approved');
                    }

                    $content->user->updateBadges();
                }
                break;

            case 'reject':
                $content->status = 'deleted';
                $content->save();
                if ($previousStatus === 'published' && $content->user) {

                    if ($type === 'post') {
                        $content->user->applyTrustChange(\App\Models\User::TRUST_SCORE_POST_PENALTY, 'Post Deleted by Admin (Report Valid)', 'admin_moderation');
                    }
                    elseif ($type === 'comment') {
                        $content->user->applyTrustChange(\App\Models\User::TRUST_SCORE_COMMENT_PENALTY, 'Comment Deleted by Admin (Report Valid)', 'admin_moderation');
                    }

                    $content->user->updateBadges();
                }
                break;

            case 'hide':
                $content->status = 'moderated';
                $content->save();
                if ($previousStatus === 'published' && $content->user) {
                     $points = $type === 'post' ? -2 : -1;
                     $content->user->applyTrustChange($points, ucfirst($type) . ' Hidden by Admin', 'admin_moderation');
                     $content->user->updateBadges();
                }
                break;
        }

        \App\Models\Report::where('reportable_id', $id)
            ->where('reportable_type', $modelClass)
            ->where('status', 'pending')
            ->update(['status' => 'resolved']);

        return response()->json(['message' => "Content processed successfully."]);
    }
}
