<?php

namespace App\Services;

use Illuminate\Support\Facades\Bus;
use App\Jobs\RunClientStatusUpdateJob;

class AdminJobService
{
    public function runClientStatusUpdate($user): void
    {
        // dispatch a job immediately (or chain jobs)
        RunClientStatusUpdateJob::dispatch($user->id ?? null);
    }
}
