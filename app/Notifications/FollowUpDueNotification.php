<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\FollowUp;

class FollowUpDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected FollowUp $followUp) {}

    public function via($notifiable) { return ['database','mail']; }

    public function toArray($notifiable)
    {
        return [
            'message' => "Follow-up due today for client: {$this->followUp->client->name}",
            'follow_up_id' => $this->followUp->id,
            'due_date' => $this->followUp->due_date->toDateString(),
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Follow-up Due Today')
            ->line("A follow-up assigned to you is due today for client: {$this->followUp->client->name}")
            ->action('Open Follow-up', url("/follow-ups/{$this->followUp->id}"));
    }
}
