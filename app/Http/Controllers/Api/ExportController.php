<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Services\ExportService;
use App\Http\Resources\ApiResponseTrait;
use Throwable;

class ExportController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private ExportService $service) {}

    public function exportClients(ExportRequest $request)
    {
        try {
            $job = $this->service->queueClientsExport($request->validated(), auth()->user());
            return $this->success($job, 'Export queued', 202);
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function status($id)
    {
        try {
            $status = $this->service->status($id);
            return $this->success($status);
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function download($id)
    {
        try {
            $file = $this->service->download($id);
            if (!$file) return $this->notFound('Export file not found');
            return response()->download($file['path'], $file['name']);
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}
