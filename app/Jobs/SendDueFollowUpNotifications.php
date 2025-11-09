<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\FollowUp;
use App\Notifications\FollowUpDueNotification;
use Illuminate\Support\Facades\Notification;

class SendDueFollowUpNotifications implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->toDateString();

        $dueFollowUps = FollowUp::with(['assignedTo','client'])
            ->whereDate('due_date', $today)
            ->where('status','pending')
            ->get();

        $dueFollowUps->each(function(FollowUp $f) {
            if ($f->assignedTo) {
                Notification::send($f->assignedTo, new FollowUpDueNotification($f));
            }
        });
    }
}


