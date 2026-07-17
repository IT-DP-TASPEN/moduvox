<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestStatusUpdated extends Notification
{
    use Queueable;

    public $type;
    public $status;

    public function __construct($type, $status)
    {
        $this->type = $type;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $statusText = $this->status == 'approved' ? 'disetujui' : 'ditolak';
        return [
            'title' => 'Status Pengajuan',
            'message' => "Pengajuan {$this->type} Anda telah {$statusText}.",
            'type' => $this->type,
            'status' => $this->status
        ];
    }
}
