<?php

declare(strict_types=1);

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AppActivityController;
use App\Http\Controllers\BugAndFeedbackController;
use App\Http\Controllers\Export\CsvController;
use App\Http\Controllers\Export\ExcelController;
use App\Http\Controllers\Import\ClockifyController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\MenubarController;
use App\Http\Controllers\Overview\DayController;
use App\Http\Controllers\Overview\MonthController;
use App\Http\Controllers\Overview\WeekController;
use App\Http\Controllers\Overview\YearController;
use App\Http\Controllers\Settings\GeneralController;
use App\Http\Controllers\Settings\StartStopController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TimestampController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WindowController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('overview.week.index'))->name('home');

Route::name('overview.')->prefix('overview')->group(function (): void {
    Route::resource('day', DayController::class)->only(['index', 'show'])->parameter('day', 'date');
    Route::resource('week', WeekController::class)->only(['index', 'show'])->parameter('week', 'date');
    Route::resource('month', MonthController::class)->only(['index', 'show'])->parameter('month', 'date');
    Route::resource('year', YearController::class)->only(['index', 'show'])->parameter('year', 'date');
});

Route::get('quit', fn () => \Native\Laravel\Facades\App::quit())->name('quit');

Route::get('welcome', [WelcomeController::class, 'index'])->name('welcome.index');
Route::patch('welcome', [WelcomeController::class, 'update'])->name('welcome.update');
Route::get('welcome/finish/{openSettings?}', [WelcomeController::class, 'finish'])->name('welcome.finish');

Route::name('menubar.')->prefix('menubar')->group(function (): void {
    Route::get('', [MenubarController::class, 'index'])->name('index');
    Route::post('break', [MenubarController::class, 'storeBreak'])->name('storeBreak');
    Route::post('work', [MenubarController::class, 'storeWork'])->name('storeWork');
    Route::post('stop', [MenubarController::class, 'storeStop'])->name('storeStop');
    Route::post('resize', [MenubarController::class, 'resize'])->name('resize');
});

Route::name('window.')->prefix('window')->group(function (): void {
    Route::get('overview/{darkMode}', [WindowController::class, 'openOverview'])->name('overview.open');
    Route::get('settings/{darkMode}', [WindowController::class, 'openSettings'])->name('settings.open');
});

Route::name('settings.')->prefix('settings')->group(function (): void {
    Route::get('', fn () => redirect()->route('settings.general.edit'))->name('index');
    Route::name('general.')->prefix('general')->group(function (): void {
        Route::get('edit', [GeneralController::class, 'edit'])->name('edit');
        Route::patch('', [GeneralController::class, 'update'])->name('update');
        Route::patch('locale', [GeneralController::class, 'updateLocale'])->name('updateLocale');
    });
    Route::name('start-stop.')->prefix('start-stop')->group(function (): void {
        Route::get('edit', [StartStopController::class, 'edit'])->name('edit');
        Route::patch('', [StartStopController::class, 'update'])->name('update');
    });
});

Route::resource('import-export', ImportExportController::class);
Route::name('import.')->prefix('import')->group(function (): void {
    Route::resource('clockify', ClockifyController::class)->only(['create', 'store']);
});
Route::name('export.')->prefix('export')->group(function (): void {
    Route::post('csv', CsvController::class)->name('csv');
    Route::post('excel', ExcelController::class)->name('excel');
});

Route::resource('work-schedule', WorkScheduleController::class)->only('index', 'create', 'store', 'edit', 'update', 'destroy');

Route::resource('app-activity', AppActivityController::class)->only(['index', 'show']);

Route::resource('tag', TagController::class);

Route::name('absence.')->prefix('absence')->group(function (): void {
    Route::get('', [AbsenceController::class, 'index'])->name('index');
    Route::get('{date}', [AbsenceController::class, 'show'])->name('show');
    Route::post('{date}', [AbsenceController::class, 'store'])->name('store');
    Route::delete('{date}/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');
});

Route::get('timestamp/create/{datetime}/{endDatetime?}', [TimestampController::class, 'create'])->name('timestamp.create')
    ->where('endDatetime', '\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}');
Route::post('timestamp/{datetime}', [TimestampController::class, 'store'])->name('timestamp.store');
Route::resource('timestamp', TimestampController::class)->only(['edit', 'update', 'destroy']);
Route::post('timestamp/fill', [TimestampController::class, 'fill'])->name('timestamp.fill');

Route::name('bug-and-feedback.')->prefix('bug-and-feedback')->group(function (): void {
    Route::get('', [BugAndFeedbackController::class, 'index'])->name('index');
    Route::get('export', [BugAndFeedbackController::class, 'export'])->name('export');
    Route::get('import', [BugAndFeedbackController::class, 'import'])->name('import');
});

Route::get('open', function (Request $request): void {
    if (\Native\Laravel\Support\Environment::isWindows()) {
        shell_exec('explorer "'.$request->string('url').'"');
    } else {
        shell_exec('open "'.$request->string('url').'"');
    }
})->name('open');

Route::get('/app-icon/{appIconName}', function ($appIconName) {
    if (! Storage::disk('app-icon')->exists($appIconName)) {
        abort(404);
    }

    return Storage::disk('app-icon')->response($appIconName, null, [
        'Cache-Control' => 'public, max-age=864000, must-revalidate',
    ]);
})->where('appIconName', '.*')->name('app-icon.show');
