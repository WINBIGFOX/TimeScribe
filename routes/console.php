<?php

declare(strict_types=1);

use App\Models\Timestamp;
use Illuminate\Support\Facades\Schedule;
use Native\Laravel\Enums\SystemIdleStatesEnum;
use Native\Laravel\Facades\PowerMonitor;

Schedule::command('menubar:refresh')->when(function () {
    return Timestamp::whereNull('ended_at')->exists();
})->everyFifteenSeconds();

Schedule::command('app:timestamp-ping')->when(function () {
    $state = PowerMonitor::getSystemIdleState(0);

    return $state === SystemIdleStatesEnum::ACTIVE;
})->everyFifteenSeconds();
Schedule::command('app:calculate-week-balance')->when(function () {
    return Timestamp::whereNull('ended_at')->exists();
})->everyMinute();
