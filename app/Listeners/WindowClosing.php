<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Laravel\Events\Windows\WindowClosed;
use Native\Laravel\Facades\Dock;
use Native\Laravel\Facades\Window;
use Native\Laravel\Support\Environment;

class WindowClosing
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
    public function handle(WindowClosed $event): void
    {
        if (Environment::isMac()) {
            $windows = Window::all();
            if (count($windows) === 0 || (count($windows) === 1 && $windows[0]->id === 'fly-timer')) {
                Dock::hide();
            }
        }
    }
}
