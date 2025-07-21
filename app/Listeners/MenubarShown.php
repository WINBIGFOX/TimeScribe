<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStopped;
use Native\Laravel\Events\MenuBar\MenuBarHidden as MenuBarHiddenEvent;
use Native\Laravel\Events\MenuBar\MenuBarShown as MenuBarShownEvent;

class MenubarShown
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
    public function handle(MenuBarShownEvent|MenuBarHiddenEvent|TimerStopped $event): void
    {
        //
    }
}
