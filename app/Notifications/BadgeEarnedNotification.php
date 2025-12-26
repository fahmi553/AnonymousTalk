<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BadgeEarnedNotification extends Notification
{
    use Queueable;

    protected $badgeName;
    protected $type;

    public function __construct($badgeName, $type = 'achievement')
    {
        $this->badgeName = $badgeName;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = "Congratulations! You earned the '{$this->badgeName}' badge.";
        $icon = 'fas fa-trophy';
        $color = 'text-primary';

        switch ($this->type) {
            case 'promotion':
                $message = "Rank Up! You have been promoted to '{$this->badgeName}'.";
                $icon = 'fas fa-arrow-up';
                $color = 'text-success';
                break;

            case 'demotion':
                $message = "Trust Score Dropped: You are now at '{$this->badgeName}' rank.";
                $icon = 'fas fa-arrow-down';
                $color = 'text-secondary';
                break;

            case 'negative':
                $message = "Alert: You have received the '{$this->badgeName}' badge due to recent activity.";
                $icon = 'fas fa-exclamation-triangle';
                $color = 'text-danger';
                break;
        }

        return [
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'link' => '/profile',
            'type' => 'badge'
        ];
    }
}
