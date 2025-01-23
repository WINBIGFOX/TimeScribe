<?php

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Services\TimestampService;
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
        if ($stopBreakAutomatic && TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
            if ($stopBreakAutomatic === 'break') {
                TimestampService::startBreak();
            }
            if ($stopBreakAutomatic === 'stop') {
                TimestampService::stop();
            }
            \Artisan::call('menubar:refresh');
        }
    }
}
