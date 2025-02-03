<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Native\Laravel\Events\PowerMonitor\ScreenLocked;
use Native\Laravel\Events\PowerMonitor\Shutdown;
use Native\Laravel\Facades\Settings;

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
        $stopBreakAutomatic = Settings::get('stopBreakAutomatic');
        if (! $stopBreakAutomatic) {
            return;
        }

        $stopBreakAutomaticActivationTime = Settings::get('stopBreakAutomaticActivationTime');

        if ($stopBreakAutomaticActivationTime) {
            if (
                ! Carbon::now()->between(
                    Carbon::now()->setTime(0, 0, 0),
                    Carbon::now()->setTime(4, 59, 59)
                )
                &&
                ! Carbon::now()->between(
                    Carbon::now()->setTime($stopBreakAutomaticActivationTime, 0, 0),
                    Carbon::now()->setTime(23, 59, 59)
                )
            ) {
                return;
            }
        }

        if (TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
            if ($stopBreakAutomatic === 'break') {
                TimestampService::startBreak();
            }
            if ($stopBreakAutomatic === 'stop') {
                TimestampService::stop();
            }
            Artisan::call('menubar:refresh');
        }
    }
}
