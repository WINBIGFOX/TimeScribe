<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded as UpdateDownloadedEvent;

class UpdateDownloaded
{
    /**
     * Handle the event.
     */
    public function handle(UpdateDownloadedEvent $event): void
    {
        $autoUpdaterSettings = resolve(AutoUpdaterSettings::class);

        $autoUpdaterSettings->lastVersion = $event->version;
        $autoUpdaterSettings->isDownloaded = true;
        $autoUpdaterSettings->save();
    }
}
