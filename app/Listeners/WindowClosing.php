<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Desktop\Events\Windows\WindowClosed;
use Native\Desktop\Facades\Dock;
use Native\Desktop\Facades\Window;
use Native\Desktop\Support\Environment;

class WindowClosing
{
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
