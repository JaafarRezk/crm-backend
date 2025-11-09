<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;   
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Client;
use App\Models\User;

class ClientsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $exportId;
    public array $filters;
    public ?int $userId;

    // أنصح بتمرير user id بدلاً من نموذج User لتفادي مشاكل التسلسل غير المرغوب فيها
    public function __construct(string $exportId, array $filters = [], ?int $userId = null)
    {
        $this->exportId = $exportId;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle()
    {
        $id = $this->exportId;

        // مثال مبسّط: تحديث حالة في الكاش
        Cache::put("export:{$id}", array_merge(Cache::get("export:{$id}", []), [
            'status' => 'processing',
            'started_at' => now()->toDateTimeString(),
        ]), now()->addDays(7));

        // جدول العملاء طبقاً للفلاتر
        $q = Client::query();
        if (!empty($this->filters['status'])) $q->where('status', $this->filters['status']);
        if (!empty($this->filters['assigned_to'])) $q->where('assigned_to', $this->filters['assigned_to']);
        if (!empty($this->filters['search'])) {
            $term = '%'.trim($this->filters['search']).'%';
            $q->where(function($s) use ($term) {
                $s->where('name','like',$term)
                  ->orWhere('email','like',$term)
                  ->orWhere('phone','like',$term);
            });
        }

        $path = "exports/clients_{$id}.csv";
        Storage::delete($path);
        $handle = fopen(storage_path("app/{$path}"), 'w');

        // header
        fputcsv($handle, ['id','name','email','phone','status','assigned_to','last_communication_at','created_at','updated_at']);

        $q->chunk(500, function($clients) use ($handle, $id) {
            foreach ($clients as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->email,
                    $c->phone,
                    $c->status,
                    $c->assigned_to,
                    $c->last_communication_at?->toDateTimeString(),
                    $c->created_at?->toDateTimeString(),
                    $c->updated_at?->toDateTimeString(),
                ]);
            }
            // يمكنك تحديث تقدم التصدير هنا إذا أردت
            Cache::put("export:{$id}.progress", 0); // مثال بسيط
        });

        fclose($handle);

        Cache::put("export:{$id}", array_merge(Cache::get("export:{$id}", []), [
            'status' => 'ready',
            'file' => $path,
            'completed_at' => now()->toDateTimeString(),
        ]), now()->addDays(7));
    }
}
