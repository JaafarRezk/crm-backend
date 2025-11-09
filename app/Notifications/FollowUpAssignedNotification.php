<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FollowUpAssignedNotification extends Notification
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
            ->subject('New Follow-up Assigned')
            ->line("A follow-up was assigned to you for client: {$clientName}")
            ->action('View Follow-up', url("/follow-ups/{$this->followUp->id}"))
            ->line('Please follow up before the due date.');
    }

    public function toArray($notifiable)
    {
        return [
            'follow_up_id' => $this->followUp->id,
            'client_id' => $this->followUp->client_id,
            'message' => "You were assigned a follow-up for client {$this->followUp->client->name}",
        ];
    }
}
