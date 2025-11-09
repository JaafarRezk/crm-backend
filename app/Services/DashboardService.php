<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function clientsByStatus(): array
    {
        return Client::select('status', DB::raw('count(*) as count'))
                     ->groupBy('status')
                     ->pluck('count','status')
                     ->toArray();
    }

    public function topSalesReps(int $limit = 5): array
    {
        // assumes clients.assigned_to references users.id
        return User::select('users.id','users.name', DB::raw('count(clients.id) as clients_count'))
                   ->join('clients','clients.assigned_to','users.id')
                   ->groupBy('users.id','users.name')
                   ->orderByDesc('clients_count')
                   ->limit($limit)
                   ->get()
                   ->toArray();
    }

    public function clientsNeedingFollowUp(): array
    {
        // reuse Client::scopeNeedsFollowUpToday
        return Client::needsFollowUpToday()->with('assignedTo')->get()->map(function($c){
            return [
                'id'=>$c->id,
                'name'=>$c->name,
                'assigned_to'=>$c->assigned_to,
                'last_communication_at'=>$c->last_communication_at?->toDateTimeString(),
            ];
        })->toArray();
    }

    public function avgCommunicationFrequency(int $days = 30): float
    {
        $since = now()->subDays($days);
        $counts = Client::withCount(['communications as recent_comms_count' => function($q) use ($since) {
            $q->where('date','>=',$since);
        }])->get()->pluck('recent_comms_count');

        if ($counts->count() === 0) return 0.0;
        return round($counts->sum() / $counts->count() / max(1, $days) * 1, 4); // avg per client per period (you can adapt)
    }

    // additional endpoints requested earlier:
    public function tasksOverduePerUser(): array
    {
        // assumes FollowUp model with assigned_to and due_date and status
        $rows = DB::table('follow_ups')
            ->select('assigned_to', DB::raw('count(*) as overdue'))
            ->where('status','pending')
            ->whereDate('due_date','<', now()->toDateString())
            ->groupBy('assigned_to')
            ->get();

        return $rows->mapWithKeys(fn($r)=>[$r->assigned_to=>$r->overdue])->toArray();
    }

    public function communicationsTrends(string $period = '7d'): array
    {
        // simple trends: count per day for last N days
        $days = $period === '30d' ? 30 : 7;
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = DB::table('communications')
            ->select(DB::raw("DATE(date) as day"), DB::raw('count(*) as total'))
            ->where('date','>=',$start)
            ->groupBy(DB::raw("DATE(date)"))
            ->orderBy('day')
            ->get()
            ->keyBy('day')
            ->toArray();

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->toDateString();
            $result[$d] = $rows[$d]->total ?? 0;
        }

        return $result;
    }
}
