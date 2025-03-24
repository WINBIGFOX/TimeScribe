<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStarted;
use Illuminate\Support\Facades\Artisan;
use Native\Laravel\Facades\Settings;

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
        if (Settings::get('appActivityTracking', false)) {
            Artisan::call('app:active-app');
        }
    }
}
