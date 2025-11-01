<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Desktop\Events\AutoUpdater\CheckingForUpdate as CheckingForUpdateEvent;

class CheckingForUpdate
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
    public function handle(CheckingForUpdateEvent $event): void
    {
        $autoUpdaterSettings = app(AutoUpdaterSettings::class);
        $autoUpdaterSettings->lastCheck = now();
        $autoUpdaterSettings->save();
    }
}
