<?php

namespace App\Listeners;

use App\Events\CommunicationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HandleCommunicationCreated implements ShouldQueue
{
    use InteractsWithQueue;


    public function handle(CommunicationCreated $event): void
    {
        $comm = $event->communication;
        $clientId = $comm->client_id;

        try {
            DB::transaction(function () use ($clientId, $comm) {
                $client = Client::lockForUpdate()->find($clientId);
                if (! $client) return;

                $client->last_communication_at = $comm->date ?? now();
                $client->saveQuietly(); 

                $since = Carbon::now()->subDays(7)->toDateTimeString();

                $count = $client->communications()
                    ->where('date', '>=', $since)
                    ->count();

                if ($count >= 3 && $client->status !== 'Hot') {
                    $old = $client->status;
                    $client->status = 'Hot';
                    $client->saveQuietly();

                    AuditLog::create([
                        'actor_id' => optional($comm->creator)->id ?: optional(auth()->user())->id,
                        'resource_type' => 'Client',
                        'resource_id' => $client->id,
                        'action' => 'auto_status_change',
                        'changes' => ['from' => $old, 'to' => 'Hot', 'reason' => '3+ communications in last 7 days'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('HandleCommunicationCreated failed', ['err' => $e->getMessage(), 'comm_id' => $comm->id]);
        }
    }
}
