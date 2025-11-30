<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Events\PauseTimerShortcutTriggered;
use App\Services\LocaleService;
use App\Services\TimestampService;
use Native\Desktop\Facades\MenuBar;

class PauseFromShortcut
{
    public function handle(PauseTimerShortcutTriggered $event): void
    {
        new LocaleService;
        $current = TimestampService::getCurrentType();

        if ($current === TimestampTypeEnum::BREAK) {
            TimestampService::startWork();

            return;
        }

        TimestampService::startBreak();
        MenuBar::show();
    }
}
