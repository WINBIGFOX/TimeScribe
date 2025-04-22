<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStopped;
use App\Models\ActivityHistory;

class AppActivityStopScan
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TimerStopped $event): void
    {
        ActivityHistory::active()->latest()->first()?->update([
            'ended_at' => now(),
        ]);
    }
}
