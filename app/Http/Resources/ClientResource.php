<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name'=> $this->name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'status'=> $this->status,
            'assigned_to'=> $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedTo', function () {
                return new UserResource($this->assignedTo);
            }),
            'last_communication_at' => $this->last_communication_at?->toDateTimeString(),
            'created_at'=> $this->created_at?->toDateTimeString(),
            'updated_at'=> $this->updated_at?->toDateTimeString(),
        ];
    }
}
