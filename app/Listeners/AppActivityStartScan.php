<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStarted;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Artisan;

class AppActivityStartScan
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
    public function handle(TimerStarted $event): void
    {
        $settings = app(GeneralSettings::class);
        if ($settings->appActivityTracking) {
            Artisan::call('app:active-app');
        }
    }
}
