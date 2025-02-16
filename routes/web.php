<?php

declare(strict_types=1);

use App\Http\Controllers\DayController;
use App\Http\Controllers\MenubarController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimestampController;
use Illuminate\Support\Facades\Route;

Route::name('menubar.')->prefix('menubar')->group(function () {
    Route::get('', [MenubarController::class, 'index'])->name('index');
    Route::post('break', [MenubarController::class, 'storeBreak'])->name('storeBreak');
    Route::post('work', [MenubarController::class, 'storeWork'])->name('storeWork');
    Route::post('stop', [MenubarController::class, 'storeStop'])->name('storeStop');
    Route::get('open-setting', [MenubarController::class, 'openSetting'])->name('openSetting');
    Route::get('open-overview', [MenubarController::class, 'openOverview'])->name('openOverview');
});

Route::name('settings.')->prefix('settings')->group(function () {
    Route::get('edit', [SettingsController::class, 'edit'])->name('edit');
    Route::patch('', [SettingsController::class, 'update'])->name('update');
});

Route::name('overview.')->prefix('overview')->group(function () {
    Route::get('', [OverviewController::class, 'index'])->name('index');
    Route::get('{date}', [OverviewController::class, 'show'])->name('show')->where('date', '\d{4}-\d{2}-\d{2}');
    Route::get('{date}/edit', [OverviewController::class, 'edit'])->name('edit')->where('date', '\d{4}-\d{2}-\d{2}');
});

Route::name('day.')->prefix('day')->group(function () {
    Route::get('{date}/edit', [DayController::class, 'edit'])->name('edit')->where('date', '\d{4}-\d{2}-\d{2}');
    Route::patch('{date}', [DayController::class, 'update'])->name('update')->where('date', '\d{4}-\d{2}-\d{2}');
});

Route::resource('timestamp', TimestampController::class)->only(['store', 'update', 'destroy']);
