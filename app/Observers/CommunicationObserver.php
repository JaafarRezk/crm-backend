<?php

namespace App\Observers;

use App\Models\Communication;

class CommunicationObserver
{
    /**
     * Handle the Communication "created" event.
     */
    public function created(Communication $communication)
    {
        $client = $communication->client;
        if ($client) {
            $client->update(['last_communication_at' => $communication->date]);
        }
    }

    /**
     * Handle the Communication "updated" event.
     */
    public function updated(Communication $communication): void
    {
        //
    }

    /**
     * Handle the Communication "deleted" event.
     */
    public function deleted(Communication $communication): void
    {
        //
    }

    /**
     * Handle the Communication "restored" event.
     */
    public function restored(Communication $communication): void
    {
        //
    }

    /**
     * Handle the Communication "force deleted" event.
     */
    public function forceDeleted(Communication $communication): void
    {
        //
    }
}
