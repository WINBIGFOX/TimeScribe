<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStarted;
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
        Artisan::call('app:active-app');
    }
}
