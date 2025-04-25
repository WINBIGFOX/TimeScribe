<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Native\Laravel\Events\App\ApplicationBooted;
use Native\Laravel\Facades\MenuBar;

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
        new LocaleService;
        $settings = app(GeneralSettings::class);
        TimestampService::checkStopTimeReset();
        CalculateWeekBalance::dispatch();
        if ($settings->showTimerOnUnlock) {
            sleep(1);
            MenuBar::clearResolvedInstances();
            MenuBar::show();
        }
    }
}
