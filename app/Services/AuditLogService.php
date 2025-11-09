<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public function paginate(array $params = null)
    {
        $q = AuditLog::query()->orderByDesc('created_at');
        if (!empty($params['resource_type'])) $q->where('resource_type', $params['resource_type']);
        if (!empty($params['actor_id'])) $q->where('actor_id', $params['actor_id']);
        $perPage = (int) ($params['per_page'] ?? 15);
        return $q->paginate($perPage);
    }

    public function find($id)
    {
        return AuditLog::find($id);
    }
}
