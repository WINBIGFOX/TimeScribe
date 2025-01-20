<?php

use App\Models\Timestamp;

Schedule::command('menubar:refresh')->when(function () {
    return Timestamp::whereNull('ended_at')->exists();
})->everyFifteenSeconds();

Schedule::command('app:timestamp-ping')->everyFifteenSeconds();
