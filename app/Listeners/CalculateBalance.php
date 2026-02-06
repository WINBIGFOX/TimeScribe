<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Jobs\CalculateWeekBalance;

class CalculateBalance
{
    /**
     * Handle the event.
     */
    public function handle(TimerStarted|TimerStopped $event): void
    {
        dispatch(new CalculateWeekBalance);
    }
}
