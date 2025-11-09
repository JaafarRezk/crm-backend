<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Http\Resources\ApiResponseTrait;
use App\Http\Resources\AuditLogResource;
use Throwable;

class AuditLogController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AuditLogService $service) {}

    public function index()
    {
        try {
            $logs = $this->service->paginate(request()->all());
            return $this->success(AuditLogResource::collection($logs), 'Audit logs');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function show($id)
    {
        try {
            $log = $this->service->find($id);
            if (!$log) return $this->notFound();
            return $this->success(new AuditLogResource($log));
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}
