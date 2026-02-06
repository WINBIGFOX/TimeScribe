<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStopped;
use Native\Desktop\Events\MenuBar\MenuBarHidden as MenuBarHiddenEvent;
use Native\Desktop\Events\MenuBar\MenuBarShown as MenuBarShownEvent;

class MenubarShown
{
    /**
     * Handle the event.
     */
    public function handle(MenuBarShownEvent|MenuBarHiddenEvent|TimerStopped $event): void
    {
        //
    }
}
