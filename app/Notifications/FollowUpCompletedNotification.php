<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FollowUpCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(protected $followUp) {}

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        $clientName = $this->followUp->client->name ?? 'client';
        return (new MailMessage)
            ->subject('Follow-up Completed')
            ->line("The follow-up for client {$clientName} has been marked as completed.")
            ->action('View Follow-up', url("/follow-ups/{$this->followUp->id}"));
    }

    public function toArray($notifiable)
    {
        return [
            'follow_up_id' => $this->followUp->id,
            'client_id' => $this->followUp->client_id,
            'message' => "Follow-up completed for client {$this->followUp->client->name}",
        ];
    }
}
