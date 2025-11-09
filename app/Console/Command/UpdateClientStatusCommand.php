<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Communication;
use App\Models\Client;

class UpdateClientStatusCommand extends Command
{
    // يجب أن يتطابق تماماً مع اسم الأمر الذي تستدعيه الاختبارات
    protected $signature = 'crm:update-client-statuses';
    protected $description = 'Update client statuses to Hot (>=3 comms in 7 days) or Inactive (>30 days since last communication)';

    public function handle(): int
    {
        DB::transaction(function () {
            $now = Carbon::now();

            $hotClientIds = Communication::query()
                ->whereNull('deleted_at')       
                ->where('date', '>=', Carbon::now()->subDays(7))
                ->groupBy('client_id')
                ->havingRaw('COUNT(*) >= ?', [3])
                ->pluck('client_id')
                ->toArray();

            if (!empty($hotClientIds)) {
                Client::whereIn('id', $hotClientIds)
                      ->update([
                          'status' => 'Hot',
                          'updated_at' => $now,
                      ]);
            }


            $inactiveQuery = Client::query()
                ->whereNotIn('id', $hotClientIds)
                ->where(function ($q) use ($now) {
                    $q->whereNull('last_communication_at')
                      ->orWhere('last_communication_at', '<', $now->copy()->subDays(30));
                });

            $inactiveQuery->update([
                'status' => 'Inactive',
                'updated_at' => Carbon::now(),
            ]);
        });

        $this->info('Client statuses updated.');
        return 0;
    }
}
