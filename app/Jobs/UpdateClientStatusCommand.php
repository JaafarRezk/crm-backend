<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AuditLog;

class UpdateClientStatusCommand extends Command
{
    protected $signature = 'crm:update-client-statuses';
    protected $description = 'Update client statuses: set Hot (>=3 comms last 7d) and Inactive (no comms 30+ days)';

    public function handle()
    {
        $this->info('Updating client statuses...');

        $sevenDays = Carbon::now()->subDays(7)->toDateTimeString();
        $thirtyDays = Carbon::now()->subDays(30)->toDateTimeString();

        DB::transaction(function() use ($sevenDays, $thirtyDays) {
            // Set Hot
            $hotClientIds = DB::table('communications')
                ->select('client_id', DB::raw('count(*) as cnt'))
                ->where('date', '>=', $sevenDays)
                ->groupBy('client_id')
                ->having('cnt', '>=', 3)
                ->pluck('client_id')
                ->toArray();

            if (!empty($hotClientIds)) {
                DB::table('clients')
                    ->whereIn('id', $hotClientIds)
                    ->where('status', '<>', 'Hot')
                    ->update(['status' => 'Hot', 'updated_at' => now()]);

                foreach ($hotClientIds as $id) {
                    AuditLog::create([
                        'actor_id' => null,
                        'resource_type' => 'Client',
                        'resource_id' => $id,
                        'action' => 'auto_status_change',
                        'changes' => json_encode(['status' => 'Hot']),
                    ]);
                }
            }

            // Set Inactive (no comms >= 30 days)
            $inactiveIds = DB::table('clients')
                ->where(function($q) use ($thirtyDays) {
                    $q->whereNull('last_communication_at')
                      ->orWhere('last_communication_at', '<', $thirtyDays);
                })
                ->pluck('id')
                ->toArray();

            if (!empty($inactiveIds)) {
                DB::table('clients')
                    ->whereIn('id', $inactiveIds)
                    ->where('status', '<>', 'Inactive')
                    ->update(['status' => 'Inactive', 'updated_at' => now()]);

                foreach ($inactiveIds as $id) {
                    AuditLog::create([
                        'actor_id' => null,
                        'resource_type' => 'Client',
                        'resource_id' => $id,
                        'action' => 'auto_status_change',
                        'changes' => json_encode(['status' => 'Inactive']),
                    ]);
                }
            }
        });

        $this->info('Client statuses updated.');
    }
}
