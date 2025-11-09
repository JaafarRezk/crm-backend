<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationRequest;
use App\Http\Requests\Communication\UpdateCommunicationRequest;
use App\Http\Resources\ApiResponseTrait;
use App\Http\Resources\CommunicationResource;
use App\Services\CommunicationService;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CommunicationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private CommunicationService $service) {}

    public function index(Client $client)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $filters = request()->only(['type','from','to']);
            $data = $this->service->list($client, $filters);
            return $this->success(CommunicationResource::collection($data));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(StoreCommunicationRequest $request, Client $client)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $comm = $this->service->create($client, $user, $request->validated());
            return $this->success(new CommunicationResource($comm), 'Communication created', 201);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(Client $client, $communication)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $comm = $this->service->show($client, (int)$communication);
            return $this->success(new CommunicationResource($comm));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateCommunicationRequest $request, Client $client, $communication)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $comm = $this->service->update($client, (int)$communication, $request->validated());
            return $this->success(new CommunicationResource($comm), 'Communication updated');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Client $client, $communication)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $this->service->delete($client, (int)$communication);
            return $this->success(null, 'Communication deleted');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function restore(Client $client, $communication)
    {
        try {
            $user = Auth::user();
            $client = Client::visibleTo($user)->findOrFail($client->id);
            $comm = $this->service->restore($client, (int)$communication);
            return $this->success(new CommunicationResource($comm), 'Communication restored');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
