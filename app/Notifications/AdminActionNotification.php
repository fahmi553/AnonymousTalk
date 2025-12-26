<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AdminActionNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'icon' => $this->type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle',
            'color' => $this->type === 'warning' ? 'text-warning' : 'text-primary'
        ];
    }
}
