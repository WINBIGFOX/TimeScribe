<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TimestampTypeEnum;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Native\Desktop\Facades\MenuBar;

class MenubarRefresh implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            new LocaleService;
            $currentType = TimestampService::getCurrentType();

            if ($currentType === TimestampTypeEnum::WORK) {
                $time = TimestampService::getWorkTime();
                MenuBar::icon(TrayIconService::getIcon('work'));
            } elseif ($currentType === TimestampTypeEnum::BREAK) {
                $time = TimestampService::getBreakTime();
                MenuBar::icon(TrayIconService::getIcon('break'));
            } else {
                MenuBar::tooltip('');
                MenuBar::label('');
                MenuBar::icon(TrayIconService::getIcon());

                return;
            }

            MenuBar::tooltip(gmdate('G:i', (int) $time));
            MenuBar::label(gmdate('G:i', (int) $time));
        } catch (\Throwable) {
            return;
        }
    }
}
