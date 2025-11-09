<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ClientsExportJob;

class ExportService
{
    public function queueClientsExport(array $filters, $user): string
    {
        $id = (string) Str::uuid();

        // status in cache (or DB if you prefer)
        Cache::put("export:{$id}", [
            'status' => 'queued',
            'queued_by' => $user->id ?? null,
            'filters' => $filters,
            'created_at' => now()->toDateTimeString(),
        ], now()->addDays(7));

        // dispatch job (async)
        ClientsExportJob::dispatch($id, $filters, auth()->id());
        return $id;
    }

    public function status(string $id): ?array
    {
        return Cache::get("export:{$id}");
    }

    public function download(string $id): ?array
    {
        $path = "exports/clients_{$id}.csv";
        if (Storage::exists($path)) {
            return [
                'path' => storage_path("app/{$path}"),
                'name' => "clients_{$id}.csv",
            ];
        }
        return null;
    }
}
