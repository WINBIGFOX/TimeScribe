<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
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
            $height = 250;
            if (app(ProjectSettings::class)->currentProject) {
                $height = 298;
            }
            MenuBar::resize(300, $height);
            MenuBar::show();
        }
    }
}
