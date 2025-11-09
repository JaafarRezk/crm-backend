<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=> $this->id,
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                ];
            }),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedTo', function () {
                return new UserResource($this->assignedTo);
            }),
            'due_date'=> $this->due_date?->toDateString(),
            'status'=> $this->status,
            'priority'=> $this->priority,
            'notes'=> $this->notes,
            'created_by'=> $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            'completed_by' => $this->completed_by,
            'completed_by_user' => $this->whenLoaded('completedBy', function () {
                return new UserResource($this->completedBy);
            }),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
