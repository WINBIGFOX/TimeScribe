<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Desktop\Events\AutoUpdater\CheckingForUpdate as CheckingForUpdateEvent;

class CheckingForUpdate
{
    /**
     * Handle the event.
     */
    public function handle(CheckingForUpdateEvent $event): void
    {
        $autoUpdaterSettings = resolve(AutoUpdaterSettings::class);
        $autoUpdaterSettings->lastCheck = now();
        $autoUpdaterSettings->save();
    }
}
