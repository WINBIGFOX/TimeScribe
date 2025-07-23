<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Jobs\CalculateWeekBalance;
use App\Jobs\MenubarRefresh;
use App\Models\Timestamp;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Native\Laravel\Enums\SystemIdleStatesEnum;
use Native\Laravel\Facades\PowerMonitor;

Artisan::command('optimize', function (): void {
    exit();
});

Schedule::when(fn () => Timestamp::whereNull('ended_at')->exists())->group(function (): void {
    Schedule::job(new MenubarRefresh)->everyFifteenSeconds();
    Schedule::job(new CalculateWeekBalance)->everyMinute();
});

Schedule::command('app:active-app')
    ->when(function (): bool {
        $settings = app(GeneralSettings::class);
        if (! $settings->appActivityTracking) {
            return false;
        }
        $isRecording = Timestamp::whereNull('ended_at')
            ->where('type', TimestampTypeEnum::WORK)
            ->exists();

        try {
            $state = PowerMonitor::getSystemIdleState(0);
        } catch (\Throwable) {
            $state = SystemIdleStatesEnum::IDLE;
        }

        return $isRecording && $state === SystemIdleStatesEnum::ACTIVE;
    })
    ->everyFiveSeconds()
    ->withoutOverlapping();

Schedule::command('app:timestamp-ping')->when(function (): bool {
    try {
        $state = PowerMonitor::getSystemIdleState(0);
    } catch (\Throwable) {
        $state = SystemIdleStatesEnum::IDLE;
    }

    return $state === SystemIdleStatesEnum::ACTIVE;
})->everyFifteenSeconds();

Schedule::command('db:optimize')
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('app:check-update')->everyTwoHours();
Schedule::command('app:check-can-install')->everyFiveMinutes();
