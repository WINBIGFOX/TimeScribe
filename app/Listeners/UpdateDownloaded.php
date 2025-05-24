<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Laravel\Events\AutoUpdater\UpdateDownloaded as UpdateDownloadedEvent;

class UpdateDownloaded
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
    public function handle(UpdateDownloadedEvent $event): void
    {
        $autoUpdaterSettings = app(AutoUpdaterSettings::class);

        $autoUpdaterSettings->lastVersion = $event->version;
        $autoUpdaterSettings->isDownloaded = true;
        $autoUpdaterSettings->save();
    }
}
