<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MenubarProjectPickerRequested;
use App\Events\OpenProjectPickerShortcutTriggered;
use App\Services\LocaleService;
use Native\Desktop\Facades\MenuBar;

class OpenProjectPickerFromShortcut
{
    /**
     * Handle the event.
     */
    public function handle(OpenProjectPickerShortcutTriggered $event): void
    {
        new LocaleService;
        MenuBar::show();
        MenubarProjectPickerRequested::broadcast();
    }
}
