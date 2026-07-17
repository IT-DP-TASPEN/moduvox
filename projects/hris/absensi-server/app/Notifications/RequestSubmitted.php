<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestSubmitted extends Notification
{
    use Queueable;

    public $type;
    public $requesterName;

    public function __construct($type, $requesterName)
    {
        $this->type = $type;
        $this->requesterName = $requesterName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pengajuan Baru',
            'message' => "Ada pengajuan {$this->type} baru dari {$this->requesterName}.",
            'type' => $this->type
        ];
    }
}
