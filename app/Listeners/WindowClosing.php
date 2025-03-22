<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Laravel\Events\Windows\WindowClosed;
use Native\Laravel\Facades\Dock;
use Native\Laravel\Facades\Window;

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
        $windows = Window::all();
        if (count($windows) === 0) {
            Dock::hide();
        }
    }
}
