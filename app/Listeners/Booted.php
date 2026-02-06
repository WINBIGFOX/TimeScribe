<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\CalculateWeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Sleep;
use Native\Desktop\Events\App\ApplicationBooted;
use Native\Desktop\Facades\MenuBar;

class Booted
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationBooted $event): void
    {
        new LocaleService;
        $settings = resolve(GeneralSettings::class);
        TimestampService::checkStopTimeReset();
        dispatch(new CalculateWeekBalance);
        if ($settings->showTimerOnUnlock) {
            Sleep::sleep(1);
            MenuBar::clearResolvedInstances();
            MenuBar::show();
        }
    }
}
