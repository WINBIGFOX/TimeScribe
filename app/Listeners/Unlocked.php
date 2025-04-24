<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Native\Laravel\Events\PowerMonitor\ScreenUnlocked;
use Native\Laravel\Facades\MenuBar;

class Unlocked
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
    public function handle(ScreenUnlocked $event): void
    {
        $settings = app(GeneralSettings::class);
        TimestampService::checkStopTimeReset();

        if ($settings->showTimerOnUnlock) {
            MenuBar::clearResolvedInstances();
            MenuBar::show();
        }

        CalculateWeekBalance::dispatch();
    }
}
