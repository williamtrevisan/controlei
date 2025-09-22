<?php

namespace App\Events;

use App\Models\Synchronization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SynchronizationCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Synchronization $synchronization,
        public int $transactionCount = 0
    ) {
    }
}
