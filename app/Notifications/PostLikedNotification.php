<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Post;

class PostLikedNotification extends Notification
{
    use Queueable;

    protected $liker;
    protected $post;

    public function __construct(User $liker, Post $post)
    {
        $this->liker = $liker;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->liker->username . ' liked your post: "' . str($this->post->title)->limit(20) . '"',
            'icon' => 'fas fa-heart',
            'color' => 'text-danger', // Red icon
            'link' => '/posts/' . $this->post->post_id,
            'type' => 'like'
        ];
    }
}
