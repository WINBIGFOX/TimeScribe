<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TimestampTypeEnum;
use App\Services\LocaleService;
use App\Services\TimeFormatService;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use App\Settings\GeneralSettings;
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
            $settings = resolve(GeneralSettings::class);
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

            $formattedTime = TimeFormatService::formatDuration($time, $settings->time_display_format ?? TimeFormatService::CLOCK);

            MenuBar::tooltip($formattedTime);
            MenuBar::label($formattedTime);
        } catch (\Throwable) {
            return;
        }
    }
}
