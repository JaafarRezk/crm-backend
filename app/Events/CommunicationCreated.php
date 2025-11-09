<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Communication;

class CommunicationCreated
{
    use Dispatchable, SerializesModels;

    public Communication $communication;

    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }
}
