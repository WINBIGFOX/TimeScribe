<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Jobs\MenubarRefresh;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Date;
use Native\Desktop\Events\PowerMonitor\ScreenLocked;
use Native\Desktop\Events\PowerMonitor\Shutdown;

class StandbyOrLocked
{
    /**
     * Handle the event.
     */
    public function handle(ScreenLocked|Shutdown $event): void
    {
        new LocaleService;
        $settings = resolve(GeneralSettings::class);
        $stopBreakAutomatic = $settings->stopBreakAutomatic;
        if (! $stopBreakAutomatic) {
            return;
        }

        $stopBreakAutomaticActivationTime = $settings->stopBreakAutomaticActivationTime;

        if ($stopBreakAutomaticActivationTime !== null && (! Date::now()->between(
            Date::now()->setTime(0, 0, 0),
            Date::now()->setTime(4, 59, 59)
        ) && ! Date::now()->between(
            Date::now()->setTime(intval($stopBreakAutomaticActivationTime), 0, 0),
            Date::now()->setTime(23, 59, 59)
        ))) {
            return;
        }

        if (TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
            if ($stopBreakAutomatic === 'break') {
                TimestampService::startBreak();
            }
            if ($stopBreakAutomatic === 'stop') {
                TimestampService::stop();
            }
            dispatch_sync(new MenubarRefresh);
        }
    }
}
