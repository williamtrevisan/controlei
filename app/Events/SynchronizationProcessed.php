<?php

namespace App\Events;

use App\Models\Synchronization;
use Illuminate\Foundation\Events\Dispatchable;

class SynchronizationProcessed
{
    use Dispatchable;

    public function __construct(
        public readonly Synchronization $synchronization,
    ) {
    }
}
