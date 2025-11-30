<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Events\StartTimerShortcutTriggered;
use App\Services\LocaleService;
use App\Services\TimestampService;
use Native\Desktop\Facades\MenuBar;

class StartFromShortcut
{
    public function handle(StartTimerShortcutTriggered $event): void
    {
        new LocaleService;
        if (TimestampService::getCurrentType() !== TimestampTypeEnum::WORK) {
            TimestampService::startWork();
        }
        MenuBar::show();
    }
}
