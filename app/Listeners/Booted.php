<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\TimestampService;
use Native\Laravel\Events\App\ApplicationBooted;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Settings;

class Booted
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
    public function handle(ApplicationBooted $event): void
    {
        TimestampService::checkStopTimeReset();
        CalculateWeekBalance::dispatch();
        if (Settings::get('showTimerOnUnlock')) {
            sleep(1);
            MenuBar::clearResolvedInstances();
            MenuBar::show();
        }
    }
}
