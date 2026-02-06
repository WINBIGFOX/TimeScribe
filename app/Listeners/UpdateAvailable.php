<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable as UpdateAvailableEvent;

class UpdateAvailable
{
    /**
     * Handle the event.
     */
    public function handle(UpdateAvailableEvent $event): void
    {
        $autoUpdaterSettings = resolve(AutoUpdaterSettings::class);
        if ($autoUpdaterSettings->lastVersion !== $event->version) {
            $autoUpdaterSettings->isDownloaded = false;
        }
        $autoUpdaterSettings->lastVersion = $event->version;
        $autoUpdaterSettings->save();
    }
}
