<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Native\Desktop\Events\PowerMonitor\ScreenUnlocked;
use Native\Desktop\Facades\MenuBar;

class Unlocked
{
    /**
     * Handle the event.
     */
    public function handle(ScreenUnlocked $event): void
    {
        new LocaleService;
        $settings = resolve(GeneralSettings::class);
        TimestampService::checkStopTimeReset();

        if ($settings->showTimerOnUnlock) {
            MenuBar::clearResolvedInstances();
            MenuBar::show();
        }

        dispatch(new CalculateWeekBalance);
    }
}
