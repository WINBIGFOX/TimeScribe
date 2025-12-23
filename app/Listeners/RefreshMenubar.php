<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Jobs\MenubarRefresh;

class RefreshMenubar
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
    public function handle(TimerStarted|TimerStopped $event): void
    {
        dispatch_sync(new MenubarRefresh);
    }
}
