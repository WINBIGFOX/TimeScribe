<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStopped;
use App\Settings\ProjectSettings;
use Native\Laravel\Events\MenuBar\MenuBarHidden as MenuBarHiddenEvent;
use Native\Laravel\Events\MenuBar\MenuBarShown as MenuBarShownEvent;
use Native\Laravel\Facades\MenuBar;

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
        $height = 250;
        if (app(ProjectSettings::class)->currentProject) {
            $height = 298;
        }
        MenuBar::resize(300, $height);
    }
}
