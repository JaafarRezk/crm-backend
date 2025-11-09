<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminJobService;
use App\Http\Resources\ApiResponseTrait;
use Throwable;

class AdminJobController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AdminJobService $service) {}

    public function runClientStatusUpdate()
    {
        try {
            $this->service->runClientStatusUpdate(auth()->user());
            return $this->success(null, 'Job dispatched');
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}


