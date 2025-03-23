<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Models\Timestamp;
use Illuminate\Support\Facades\Schedule;
use Native\Laravel\Enums\SystemIdleStatesEnum;
use Native\Laravel\Facades\PowerMonitor;
use Native\Laravel\Facades\Settings;

Schedule::when(fn () => Timestamp::whereNull('ended_at')->exists())->group(function () {
    Schedule::command('menubar:refresh')->everyFifteenSeconds();
    Schedule::command('app:calculate-week-balance')->everyMinute();
});

Schedule::command('app:active-app')
    ->when(function () {
        $isRecording = Timestamp::whereNull('ended_at')
            ->where('type', TimestampTypeEnum::WORK)
            ->exists();
        $state = PowerMonitor::getSystemIdleState(0);

        return $isRecording && $state === SystemIdleStatesEnum::ACTIVE && Settings::get('appActivityTracking', false);
    })
    ->everyFiveSeconds()
    ->withoutOverlapping();

Schedule::command('app:timestamp-ping')->when(function () {
    $state = PowerMonitor::getSystemIdleState(0);

    return $state === SystemIdleStatesEnum::ACTIVE;
})->everyFifteenSeconds();
