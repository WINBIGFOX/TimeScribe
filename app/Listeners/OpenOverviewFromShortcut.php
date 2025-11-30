<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OpenOverviewShortcutTriggered;
use App\Services\WindowService;

class OpenOverviewFromShortcut
{
    public function handle(OpenOverviewShortcutTriggered $event): void
    {
        // Default to light mode if not known; frontends may re-open with current theme.
        WindowService::openHome(darkMode: false);
    }
}
