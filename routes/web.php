<?php

declare(strict_types=1);

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\MenubarController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimestampController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('welcome', [WelcomeController::class, 'index'])->name('welcome.index');
Route::patch('welcome', [WelcomeController::class, 'update'])->name('welcome.update');
Route::get('welcome/finish/{openSettings?}', [WelcomeController::class, 'finish'])->name('welcome.finish');

Route::name('menubar.')->prefix('menubar')->group(function () {
    Route::get('', [MenubarController::class, 'index'])->name('index');
    Route::post('break', [MenubarController::class, 'storeBreak'])->name('storeBreak');
    Route::post('work', [MenubarController::class, 'storeWork'])->name('storeWork');
    Route::post('stop', [MenubarController::class, 'storeStop'])->name('storeStop');
    Route::get('open-setting/{darkMode}', [MenubarController::class, 'openSetting'])->name('openSetting');
    Route::get('open-overview/{darkMode}', [MenubarController::class, 'openOverview'])->name('openOverview');
    Route::get('open-absence/{darkMode}', [MenubarController::class, 'openAbsence'])->name('openAbsence');
});

Route::name('settings.')->prefix('settings')->group(function () {
    Route::get('edit', [SettingsController::class, 'edit'])->name('edit');
    Route::patch('', [SettingsController::class, 'update'])->name('update');
    Route::patch('locale', [SettingsController::class, 'updateLocale'])->name('updateLocale');
});

Route::resource('work-schedule', WorkScheduleController::class)->only('create', 'store', 'edit', 'update', 'destroy');

Route::name('overview.')->prefix('overview')->group(function () {
    Route::get('', [OverviewController::class, 'index'])->name('index');
    Route::get('{date}', [OverviewController::class, 'show'])->name('show')->where('date', '\d{4}-\d{2}-\d{2}');
    Route::get('{date}/edit/{darkMode}', [OverviewController::class, 'edit'])->name('edit')->where('date', '\d{4}-\d{2}-\d{2}');
});

Route::name('day.')->prefix('day')->group(function () {
    Route::get('{date}/edit', [DayController::class, 'edit'])->name('edit')->where('date', '\d{4}-\d{2}-\d{2}');
    Route::patch('{date}', [DayController::class, 'update'])->name('update')->where('date', '\d{4}-\d{2}-\d{2}');
});

Route::name('absence.')->prefix('absence')->group(function () {
    Route::get('', [AbsenceController::class, 'index'])->name('index');
    Route::get('{date}', [AbsenceController::class, 'show'])->name('show');
    Route::post('{date}', [AbsenceController::class, 'store'])->name('store');
    Route::delete('{date}/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');
});

Route::resource('timestamp', TimestampController::class)->only(['edit', 'update', 'destroy']);
Route::post('timestamp/fill', [TimestampController::class, 'fill'])->name('timestamp.fill');

Route::get('/app-icon/{appIconName}', function ($appIconName) {
    if (! Storage::disk('app-icon')->exists($appIconName)) {
        abort(404);
    }

    return Storage::disk('app-icon')->response($appIconName, null, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('appIconName', '.*')->name('app-icon.show');
