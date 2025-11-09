<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'actor_id'=>$this->actor_id,
            'resource_type'=>$this->resource_type,
            'resource_id'=>$this->resource_id,
            'action'=>$this->action,
            'changes'=>json_decode($this->changes, true),
            'created_at'=>$this->created_at?->toDateTimeString(),
        ];
    }
}
