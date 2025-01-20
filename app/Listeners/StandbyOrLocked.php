<?php

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Services\TimestampService;
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
        if (TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
            TimestampService::stop();
            \Artisan::call('menubar:refresh');
        }
    }
}
