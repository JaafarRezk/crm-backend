<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Requests\Client\AssignBulkRequest;
use App\Http\Requests\Client\RestoreBulkRequest;
use App\Http\Resources\ApiResponseTrait;
use App\Services\ClientService;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Throwable;
use App\Http\Resources\ClientResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{
    use AuthorizesRequests, ApiResponseTrait;
    public function __construct(private ClientService $service) {}

    public function index()
    {
        try {
            $user = Auth::user();
            $filters = request()->only(['status','assigned_to','search']);
            $perPage = (int) request()->get('per_page', 15);
            $result = $this->service->list($user, $filters, $perPage);
            // return paginated resource
            return $this->success(\App\Http\Resources\ClientResource::collection($result));
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function store(StoreClientRequest $request)
    {
        try {
            $user = Auth::user();
            $this->authorize('create', Client::class);

            $client = $this->service->create($request->validated(), $user);
            return $this->success(new ClientResource($client), 'Client created', 201);
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function show(Client $client)
    {
        try {
            $user = Auth::user();
            $this->authorize('view', $client);

            $client = Client::with(['assignedTo','communications','followUps'])->findOrFail($client->id);
            return $this->success(new ClientResource($client));
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        try {
            $user = Auth::user();
            $this->authorize('update', $client);

            $client = $this->service->update($client, $request->validated(), $user);
            return $this->success(new ClientResource($client), 'Client updated');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function destroy(Client $client)
    {
        try {
            $user = Auth::user();
            $this->authorize('delete', $client);

            $this->service->delete($client);
            return $this->success(null, 'Client deleted');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    public function restore($id)
    {
        try {
            $client = Client::withTrashed()->findOrFail($id);
            $this->authorize('restore', $client);

            $this->service->restore($client);
            return $this->success(new ClientResource($client), 'Client restored');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    // Bulk assign
    public function assignBulk(AssignBulkRequest $request)
    {
        try {
            $user = Auth::user();
            $this->authorize('assignBulk', Client::class);

            $data = $request->validated();
            $clients = $this->service->assignBulk($data['client_ids'], $data['assigned_to'], $user);

            return $this->success(\App\Http\Resources\ClientResource::collection($clients), 'Clients assigned');
        } catch (Throwable $e) { return $this->handleException($e); }
    }

    // Bulk restore
    public function restoreBulk(RestoreBulkRequest $request)
    {
        try {
            $user = Auth::user();
            $this->authorize('restoreBulk', Client::class);

            $data = $request->validated();
            $clients = $this->service->restoreBulk($data['client_ids'], $user);

            return $this->success(\App\Http\Resources\ClientResource::collection($clients), 'Clients restored');
        } catch (Throwable $e) { return $this->handleException($e); }
    }
}


