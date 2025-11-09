<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\ApiResponseTrait;
use Throwable;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private NotificationService $service) {}

    public function index()
    {
        try {
            $list = $this->service->forUser(auth()->user(), request()->all());
            return $this->success(NotificationResource::collection($list));
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function markRead()
    {
        try {
            $ids = (array) request('ids', []);
            $this->service->markRead(auth()->user(), $ids);
            return $this->success(null, 'Marked read');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function markAllRead()
    {
        try {
            $this->service->markAllRead(auth()->user());
            return $this->success(null, 'All marked read');
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}
