<?php

namespace App\Listeners;

use Native\Laravel\Events\App\ApplicationBooted;
use Native\Laravel\Events\PowerMonitor\ScreenUnlocked;
use Native\Laravel\Facades\MenuBar;

class UnlockedOrBooted
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
    public function handle(ScreenUnlocked|ApplicationBooted $event): void
    {
        MenuBar::clearResolvedInstances();
        MenuBar::show();
    }
}
