<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Desktop\Events\Windows\WindowShown;
use Native\Desktop\Facades\Dock;
use Native\Desktop\Support\Environment;

class WindowOpening
{
    /**
     * Handle the event.
     */
    public function handle(WindowShown $event): void
    {
        if (Environment::isMac() && ! $event->id === 'fly-timer') {
            Dock::show();
        }
    }
}
