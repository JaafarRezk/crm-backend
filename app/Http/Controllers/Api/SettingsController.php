<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsUpdateRequest;
use App\Services\SettingsService;
use App\Http\Resources\ApiResponseTrait;
use Throwable;

class SettingsController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private SettingsService $service) {}

    public function index()
    {
        try {
            $settings = $this->service->all();
            return $this->success($settings);
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function show($key)
    {
        try {
            $value = $this->service->get($key);
            if ($value === null) return $this->notFound();
            return $this->success($value);
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function update(SettingsUpdateRequest $request)
    {
        try {
            $data = $this->service->updateMany($request->validated());
            return $this->success($data, 'Settings updated');
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}
