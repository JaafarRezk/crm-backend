<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUp\StoreFollowUpRequest;
use App\Http\Requests\FollowUp\UpdateFollowUpRequest;
use App\Http\Resources\ApiResponseTrait;
use App\Services\FollowUpService;
use App\Models\FollowUp;
use Illuminate\Support\Facades\Auth;
use Throwable;
use App\Http\Resources\FollowUpResource;

class FollowUpController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private FollowUpService $service) {}

    public function index()
    {
        try {
            $user = Auth::user();
            $filters = request()->only(['due_date', 'status', 'search', 'assigned_to', 'per_page', 'page']);
            $data = $this->service->list($user, $filters);

            // FollowUpResource::collection($data) مع paginator ينتج meta/links عند الاستجابة التلقائية
            $resource = \App\Http\Resources\FollowUpResource::collection($data);
            $resourceArray = $resource->response()->getData(true);

            return response()->json([
                'status' => 'success',
                'message' => 'Follow-ups fetched',
                'data' => $resourceArray['data'],
                'meta' => $resourceArray['meta'] ?? null,
                'links' => $resourceArray['links'] ?? null,
            ], 200);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(StoreFollowUpRequest $request)
    {
        try {
            $user = Auth::user();
            // optional: validate assignee access to client here
            $follow = $this->service->create($request->validated(), $user);
            return $this->success(new FollowUpResource($follow), 'Follow-up created', 201);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(FollowUp $follow_up)
    {
        try {
            $user = Auth::user();
            // Authorization: sales reps only see their assigned tasks
            if ($user->isSalesRep() && $follow_up->assigned_to != $user->id) {
                return $this->forbidden('Not allowed');
            }
            return $this->success(new FollowUpResource($follow_up->load(['client', 'assignedTo', 'creator', 'completedBy'])));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateFollowUpRequest $request, FollowUp $follow_up)
    {
        try {
            $user = Auth::user();
            if (! $user->isAdmin() && $user->id !== $follow_up->assigned_to && $user->id !== $follow_up->created_by) {
                return $this->forbidden('Not allowed to update this follow-up.');
            }
            $follow = $this->service->update($follow_up, $request->validated(), $user);
            return $this->success(new FollowUpResource($follow), 'Follow-up updated');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(FollowUp $follow_up)
    {
        try {
            $user = Auth::user();
            if (! $user->isAdmin() && $user->id !== $follow_up->created_by) {
                return $this->forbidden('Not allowed to delete this follow-up.');
            }

            $this->service->delete($follow_up, $user);
            return $this->success(null, 'Follow-up deleted');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $user = Auth::user();
            if (! $user->isAdmin()) {
                return $this->forbidden('Only admin can restore follow-ups.');
            }
            $follow = $this->service->restore((int)$id, $user);
            return $this->success(new FollowUpResource($follow), 'Follow-up restored');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function markComplete($id)
    {
        try {
            $user = Auth::user();
            $follow = $this->service->markComplete((int)$id, $user);
            return $this->success(new FollowUpResource($follow), 'Follow-up marked complete');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function markCancelled($id)
    {
        try {
            $user = Auth::user();
            $follow = $this->service->markCancelled((int)$id, $user);
            return $this->success(new FollowUpResource($follow), 'Follow-up cancelled');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function myTasks()
    {
        try {
            $user = Auth::user();
            $filters = request()->only(['due_date', 'status']);

            $list = $this->service->forUser($user, $filters);


            if ($list instanceof \Illuminate\Pagination\LengthAwarePaginator || $list instanceof \Illuminate\Pagination\Paginator) {
                $list->getCollection()->load(['client', 'assignedTo', 'creator', 'completedBy']);
                return $this->success(\App\Http\Resources\FollowUpResource::collection($list));
            }

            if ($list instanceof \Illuminate\Database\Eloquent\Collection) {
                $list->load(['client', 'assignedTo', 'creator', 'completedBy']);
                return $this->success(\App\Http\Resources\FollowUpResource::collection($list));
            }

            return $this->success(\App\Http\Resources\FollowUpResource::collection(collect($list)));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
