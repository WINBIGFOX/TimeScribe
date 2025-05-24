<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Settings\AutoUpdaterSettings;
use Native\Laravel\Events\AutoUpdater\UpdateAvailable as UpdateAvailableEvent;

class UpdateAvailable
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
    public function handle(UpdateAvailableEvent $event): void
    {
        $autoUpdaterSettings = app(AutoUpdaterSettings::class);
        if ($autoUpdaterSettings->lastVersion !== $event->version) {
            $autoUpdaterSettings->isDownloaded = false;
        }
        $autoUpdaterSettings->lastVersion = $event->version;
        $autoUpdaterSettings->save();
    }
}
