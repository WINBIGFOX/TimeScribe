<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Jobs\MenubarRefresh;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Native\Laravel\Events\PowerMonitor\ScreenLocked;
use Native\Laravel\Events\PowerMonitor\Shutdown;

class StandbyOrLocked
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
    public function handle(ScreenLocked|Shutdown $event): void
    {
        new LocaleService;
        $settings = app(GeneralSettings::class);
        $stopBreakAutomatic = $settings->stopBreakAutomatic;
        if (! $stopBreakAutomatic) {
            return;
        }

        $stopBreakAutomaticActivationTime = $settings->stopBreakAutomaticActivationTime;

        if ($stopBreakAutomaticActivationTime !== null && (! Carbon::now()->between(
            Carbon::now()->setTime(0, 0, 0),
            Carbon::now()->setTime(4, 59, 59)
        ) && ! Carbon::now()->between(
            Carbon::now()->setTime(intval($stopBreakAutomaticActivationTime), 0, 0),
            Carbon::now()->setTime(23, 59, 59)
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
            MenubarRefresh::dispatchSync();
        }
    }
}
