<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Laravel\Events\MenuBar\MenuBarHidden as MenuBarHiddenEvent;
use Native\Laravel\Facades\MenuBar;

class MenubarHidden
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
    public function handle(MenuBarHiddenEvent $event): void
    {
        MenuBar::resize(300, 298);
    }
}
