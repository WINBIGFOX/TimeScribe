<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStopped;
use App\Models\ActivityHistory;
use App\Services\LocaleService;

class AppActivityStopScan
{
    /**
     * Handle the event.
     */
    public function handle(TimerStopped $event): void
    {
        new LocaleService;
        ActivityHistory::active()->latest()->first()?->update([
            'ended_at' => now(),
        ]);
    }
}
