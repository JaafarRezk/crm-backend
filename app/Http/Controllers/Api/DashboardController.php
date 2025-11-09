<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Services\DashboardService;
use Throwable;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private DashboardService $service) {}

    // New single endpoint to return all dashboard data
    public function summary()
    {
        try {
            $limit = request()->get('limit', 5);
            $days = request()->get('days', 30);

            $data = [
                'clients_by_status' => $this->service->clientsByStatus(),
                'top_sales_reps' => $this->service->topSalesReps($limit),
                'clients_needing_follow_up' => $this->service->clientsNeedingFollowUp(),
                'avg_communication_frequency' => [
                    'avg_per_client' => $this->service->avgCommunicationFrequency((int)$days)
                ],
                'tasks_overdue_per_user' => $this->service->tasksOverduePerUser(),
                'communications_trends' => $this->service->communicationsTrends(),
            ];

            return $this->success($data);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
