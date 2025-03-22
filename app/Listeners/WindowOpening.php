<?php

declare(strict_types=1);

namespace App\Listeners;

use Native\Laravel\Events\Windows\WindowShown;
use Native\Laravel\Facades\Dock;

class WindowOpening
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
    public function handle(WindowShown $event): void
    {
        Dock::show();
    }
}
