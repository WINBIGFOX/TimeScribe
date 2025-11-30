<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TimestampTypeEnum;
use App\Events\StopTimerShortcutTriggered;
use App\Services\LocaleService;
use App\Services\TimestampService;
use Native\Desktop\Facades\MenuBar;

class StopFromShortcut
{
    public function handle(StopTimerShortcutTriggered $event): void
    {
        new LocaleService;
        $current = TimestampService::getCurrentType();
        if ($current === TimestampTypeEnum::BREAK || $current === TimestampTypeEnum::WORK) {
            TimestampService::stop();
        }
        MenuBar::show();
    }
}
