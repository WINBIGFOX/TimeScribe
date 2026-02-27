<?php

declare(strict_types=1);

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AppActivityController;
use App\Http\Controllers\BugAndFeedbackController;
use App\Http\Controllers\Export\ExportController;
use App\Http\Controllers\FlyTimerController;
use App\Http\Controllers\HolidayRuleController;
use App\Http\Controllers\Import\ClockifyController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\MenubarController;
use App\Http\Controllers\OvertimeAdjustment\OvertimeAdjustmentController;
use App\Http\Controllers\Overview\DayController;
use App\Http\Controllers\Overview\MonthController;
use App\Http\Controllers\Overview\WeekController;
use App\Http\Controllers\Overview\YearController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Settings\GeneralController;
use App\Http\Controllers\Settings\ShortcutController;
use App\Http\Controllers\Settings\StartStopController;
use App\Http\Controllers\Settings\VacationController as SettingsVacationController;
use App\Http\Controllers\TimestampController;
use App\Http\Controllers\UpdaterController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\VacationEntitlementController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WindowController;
use App\Http\Controllers\WorkScheduleController;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Native\Desktop\Facades\App;
use Native\Desktop\Support\Environment;

Route::get('/', function (GeneralSettings $settings): Redirector|RedirectResponse {
    $target = $settings->default_overview ?? 'week';

    $route = match ($target) {
        'day' => 'overview.day.index',
        'month' => 'overview.month.index',
        'year' => 'overview.year.index',
        default => 'overview.week.index',
    };

    return to_route($route);
})->name('home');

Route::name('overview.')->prefix('overview')->group(function (): void {
    Route::resource('day', DayController::class)->only(['index', 'show'])->parameter('day', 'date');
    Route::resource('week', WeekController::class)->only(['index', 'show'])->parameter('week', 'date');
    Route::resource('month', MonthController::class)->only(['index', 'show'])->parameter('month', 'date');
    Route::resource('year', YearController::class)->only(['index', 'show'])->parameter('year', 'date');
});

Route::name('overtime-adjustment.')->prefix('overtime-adjustment')->group(function (): void {
    Route::get('{date}', [OvertimeAdjustmentController::class, 'show'])->name('show');
    Route::post('{date}', [OvertimeAdjustmentController::class, 'store'])->name('store');
    Route::patch('{date}/{overtimeAdjustment}', [OvertimeAdjustmentController::class, 'update'])->name('update');
    Route::delete('{date}/{overtimeAdjustment}', [OvertimeAdjustmentController::class, 'destroy'])->name('destroy');
});

Route::get('quit', fn () => App::quit())->name('quit');

Route::get('welcome', [WelcomeController::class, 'index'])->name('welcome.index');
Route::patch('welcome', [WelcomeController::class, 'update'])->name('welcome.update');
Route::get('welcome/finish/{openSettings?}', [WelcomeController::class, 'finish'])->name('welcome.finish');

Route::name('menubar.')->prefix('menubar')->group(function (): void {
    Route::get('', [MenubarController::class, 'index'])->name('index');
    Route::post('break', [MenubarController::class, 'storeBreak'])->name('storeBreak');
    Route::post('work', [MenubarController::class, 'storeWork'])->name('storeWork');
    Route::post('stop', [MenubarController::class, 'storeStop'])->name('storeStop');
    Route::post('set-project/{project}', [MenubarController::class, 'setProject'])->name('set-project');
    Route::post('remove-project', [MenubarController::class, 'removeProject'])->name('remove-project');
});

Route::name('fly-timer.')->prefix('fly-timer')->group(function (): void {
    Route::get('', [FlyTimerController::class, 'index'])->name('index');
    Route::post('work', [FlyTimerController::class, 'storeBreak'])->name('storeBreak');
    Route::post('break', [FlyTimerController::class, 'storeWork'])->name('storeWork');
    Route::post('stop', [FlyTimerController::class, 'storeStop'])->name('storeStop');
});

Route::name('window.')->prefix('window')->group(function (): void {
    Route::get('updater/{darkMode}', [WindowController::class, 'openUpdater'])->name('updater.open');
    Route::get('overview/{darkMode}', [WindowController::class, 'openOverview'])->name('overview.open');
    Route::get('settings/{darkMode}', [WindowController::class, 'openSettings'])->name('settings.open');
    Route::get('new-project/{darkMode}', [WindowController::class, 'openNewProject'])->name('new-project.open');
    Route::get('fly-timer/open', [WindowController::class, 'openFlyTimer'])->name('fly-timer.open');
    Route::get('fly-timer/close', [WindowController::class, 'closeFlyTimer'])->name('fly-timer.close');
});

Route::name('settings.')->prefix('settings')->group(function (): void {
    Route::get('', fn (): Redirector|RedirectResponse => to_route('settings.general.edit'))->name('index');
    Route::name('general.')->prefix('general')->group(function (): void {
        Route::get('edit', [GeneralController::class, 'edit'])->name('edit');
        Route::patch('', [GeneralController::class, 'update'])->name('update');
        Route::patch('locale', [GeneralController::class, 'updateLocale'])->name('updateLocale');
    });
    Route::name('start-stop.')->prefix('start-stop')->group(function (): void {
        Route::get('edit', [StartStopController::class, 'edit'])->name('edit');
        Route::patch('', [StartStopController::class, 'update'])->name('update');
    });
    Route::name('shortcuts.')->prefix('shortcuts')->group(function (): void {
        Route::get('edit', [ShortcutController::class, 'edit'])->name('edit');
        Route::patch('', [ShortcutController::class, 'update'])->name('update');
    });
    Route::name('vacation.')->prefix('vacation')->group(function (): void {
        Route::get('edit', [SettingsVacationController::class, 'edit'])->name('edit');
        Route::patch('', [SettingsVacationController::class, 'update'])->name('update');
    });
});

Route::name('updater.')->prefix('updater')->group(function (): void {
    Route::get('', [UpdaterController::class, 'index'])->name('index');
    Route::patch('auto-update', [UpdaterController::class, 'updateAutoUpdate'])->name('updateAutoUpdate');
    Route::post('install', [UpdaterController::class, 'install'])->name('install');
    Route::post('check', [UpdaterController::class, 'check'])->name('check');
});

Route::resource('import-export', ImportExportController::class);
Route::name('import.')->prefix('import')->group(function (): void {
    Route::resource('clockify', ClockifyController::class)->only(['create', 'store']);
});
Route::singleton('export', ExportController::class)->creatable()->only(['create', 'store']);

Route::resource('work-schedule', WorkScheduleController::class)->only('index', 'create', 'store', 'edit', 'update', 'destroy');

Route::resource('app-activity', AppActivityController::class)->only(['index', 'show']);

Route::patch('project/{project}/restore', [ProjectController::class, 'restore'])->name('project.restore')->withTrashed();
Route::resource('project', ProjectController::class)->withTrashed();

Route::name('absence.')->prefix('absence')->group(function (): void {
    Route::get('', [AbsenceController::class, 'index'])->name('index');
    Route::get('{date}', [AbsenceController::class, 'show'])->name('show');
    Route::post('{date}', [AbsenceController::class, 'store'])->name('store');
    Route::delete('{date}/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');

    Route::post('holiday-rule', [HolidayRuleController::class, 'store'])->name('holiday-rule.store');
    Route::delete('holiday-rule', [HolidayRuleController::class, 'destroy'])->name('holiday-rule.destroy');
    Route::get('vacation/{date?}', [VacationController::class, 'index'])->name('vacation.index');
    Route::delete('vacation/{absence}', [VacationController::class, 'destroy'])->name('vacation.destroy');
    Route::get('vacation-entitlement/{date}', [VacationEntitlementController::class, 'edit'])->name('vacation-entitlement.edit');
    Route::post('vacation-entitlement', [VacationEntitlementController::class, 'update'])->name('vacation-entitlement.update');
});

Route::get('timestamp/create/{datetime}/{endDatetime?}/{type?}', [TimestampController::class, 'create'])->name('timestamp.create')
    ->where('endDatetime', '\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}');
Route::post('timestamp/{datetime}', [TimestampController::class, 'store'])->name('timestamp.store');
Route::patch('timestamp/merge', [TimestampController::class, 'merge'])->name('timestamp.merge');
Route::resource('timestamp', TimestampController::class)->only(['edit', 'update', 'destroy']);
Route::patch('timestamp/{timestamp}/paid', [TimestampController::class, 'updatePaid'])->name('timestamp.update.paid');
Route::post('timestamp/fill', [TimestampController::class, 'fill'])->name('timestamp.fill');

Route::name('bug-and-feedback.')->prefix('bug-and-feedback')->group(function (): void {
    Route::get('', [BugAndFeedbackController::class, 'index'])->name('index');
    Route::get('export', [BugAndFeedbackController::class, 'export'])->name('export');
    Route::get('import', [BugAndFeedbackController::class, 'import'])->name('import');
    Route::delete('delete-all', [BugAndFeedbackController::class, 'destroy'])->name('delete-all');
});

Route::get('open', function (Request $request): void {
    $url = $request->string('url')->toString();
    if (Environment::isWindows()) {
        shell_exec('explorer '.escapeshellarg($url));
    } else {
        shell_exec('open '.escapeshellarg($url));
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
