<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected $commenter;
    protected $post;
    protected $parentComment;

    public function __construct(User $commenter, Post $post, ?Comment $parentComment = null)
    {
        $this->commenter = $commenter;
        $this->post = $post;
        $this->parentComment = $parentComment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        if ($this->parentComment) {
            $msg = $this->commenter->username . ' replied to your comment: "' . str($this->parentComment->content)->limit(20) . '"';
            $icon = 'fas fa-reply';
        } else {
            $msg = $this->commenter->username . ' commented on your post: "' . str($this->post->title)->limit(20) . '"';
            $icon = 'fas fa-comment-dots';
        }

        return [
            'message' => $msg,
            'icon' => $icon,
            'color' => 'text-info',
            'link' => '/posts/' . $this->post->post_id,
            'type' => 'comment'
        ];
    }
}
