<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Models\Timestamp;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Schedule;
use Native\Laravel\Enums\SystemIdleStatesEnum;
use Native\Laravel\Facades\PowerMonitor;

Artisan::command('optimize', function () {
    exit();
});

Schedule::when(fn () => Timestamp::whereNull('ended_at')->exists())->group(function (): void {
    Schedule::command('menubar:refresh')->everyFifteenSeconds();
    Schedule::command('app:calculate-week-balance')->everyMinute();
});

Schedule::command('app:active-app')
    ->when(function (): bool {
        $settings = app(GeneralSettings::class);
        $isRecording = Timestamp::whereNull('ended_at')
            ->where('type', TimestampTypeEnum::WORK)
            ->exists();
        $state = PowerMonitor::getSystemIdleState(0);

        return $isRecording && $state === SystemIdleStatesEnum::ACTIVE && $settings->appActivityTracking;
    })
    ->everyFiveSeconds()
    ->withoutOverlapping();

Schedule::command('app:timestamp-ping')->when(function (): bool {
    $state = PowerMonitor::getSystemIdleState(0);

    return $state === SystemIdleStatesEnum::ACTIVE;
})->everyFifteenSeconds();

Schedule::command('db:optimize')
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();
