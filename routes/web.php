<?php

declare(strict_types=1);

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AppActivityController;
use App\Http\Controllers\BugAndFeedbackController;
use App\Http\Controllers\Export\CsvController;
use App\Http\Controllers\Export\ExcelController;
use App\Http\Controllers\FlyTimerController;
use App\Http\Controllers\HolidayRuleController;
use App\Http\Controllers\Import\ClockifyController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\MenubarController;
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

Route::get('quit', fn () => App::quit())->name('quit');

Route::get('welcome', new WelcomeController()->index(...))->name('welcome.index');
Route::patch('welcome', new WelcomeController()->update(...))->name('welcome.update');
Route::get('welcome/finish/{openSettings?}', new WelcomeController()->finish(...))->name('welcome.finish');

Route::name('menubar.')->prefix('menubar')->group(function (): void {
    Route::get('', new MenubarController()->index(...))->name('index');
    Route::post('break', new MenubarController()->storeBreak(...))->name('storeBreak');
    Route::post('work', new MenubarController()->storeWork(...))->name('storeWork');
    Route::post('stop', new MenubarController()->storeStop(...))->name('storeStop');
    Route::post('set-project/{project}', new MenubarController()->setProject(...))->name('set-project');
    Route::post('remove-project', new MenubarController()->removeProject(...))->name('remove-project');
});

Route::name('fly-timer.')->prefix('fly-timer')->group(function (): void {
    Route::get('', new FlyTimerController()->index(...))->name('index');
    Route::post('work', new FlyTimerController()->storeBreak(...))->name('storeBreak');
    Route::post('break', new FlyTimerController()->storeWork(...))->name('storeWork');
    Route::post('stop', new FlyTimerController()->storeStop(...))->name('storeStop');
});

Route::name('window.')->prefix('window')->group(function (): void {
    Route::get('updater/{darkMode}', new WindowController()->openUpdater(...))->name('updater.open');
    Route::get('overview/{darkMode}', new WindowController()->openOverview(...))->name('overview.open');
    Route::get('settings/{darkMode}', new WindowController()->openSettings(...))->name('settings.open');
    Route::get('new-project/{darkMode}', new WindowController()->openNewProject(...))->name('new-project.open');
    Route::get('fly-timer/open', new WindowController()->openFlyTimer(...))->name('fly-timer.open');
    Route::get('fly-timer/close', new WindowController()->closeFlyTimer(...))->name('fly-timer.close');
});

Route::name('settings.')->prefix('settings')->group(function (): void {
    Route::get('', fn (): Redirector|RedirectResponse => to_route('settings.general.edit'))->name('index');
    Route::name('general.')->prefix('general')->group(function (): void {
        Route::get('edit', new GeneralController()->edit(...))->name('edit');
        Route::patch('', new GeneralController()->update(...))->name('update');
        Route::patch('locale', new GeneralController()->updateLocale(...))->name('updateLocale');
    });
    Route::name('start-stop.')->prefix('start-stop')->group(function (): void {
        Route::get('edit', new StartStopController()->edit(...))->name('edit');
        Route::patch('', new StartStopController()->update(...))->name('update');
    });
    Route::name('shortcuts.')->prefix('shortcuts')->group(function (): void {
        Route::get('edit', new ShortcutController()->edit(...))->name('edit');
        Route::patch('', new ShortcutController()->update(...))->name('update');
    });
    Route::name('vacation.')->prefix('vacation')->group(function (): void {
        Route::get('edit', new SettingsVacationController()->edit(...))->name('edit');
        Route::patch('', new SettingsVacationController()->update(...))->name('update');
    });
});

Route::name('updater.')->prefix('updater')->group(function (): void {
    Route::get('', new UpdaterController()->index(...))->name('index');
    Route::patch('auto-update', new UpdaterController()->updateAutoUpdate(...))->name('updateAutoUpdate');
    Route::post('install', new UpdaterController()->install(...))->name('install');
    Route::post('check', new UpdaterController()->check(...))->name('check');
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

Route::patch('project/{project}/restore', new ProjectController()->restore(...))->name('project.restore')->withTrashed();
Route::resource('project', ProjectController::class)->withTrashed();

Route::name('absence.')->prefix('absence')->group(function (): void {
    Route::get('', new AbsenceController()->index(...))->name('index');
    Route::get('{date}', new AbsenceController()->show(...))->name('show');
    Route::post('{date}', new AbsenceController()->store(...))->name('store');
    Route::delete('{date}/{absence}', new AbsenceController()->destroy(...))->name('destroy');

    Route::post('holiday-rule', new HolidayRuleController()->store(...))->name('holiday-rule.store');
    Route::delete('holiday-rule', new HolidayRuleController()->destroy(...))->name('holiday-rule.destroy');
    Route::get('vacation/{date?}', new VacationController()->index(...))->name('vacation.index');
    Route::delete('vacation/{absence}', new VacationController()->destroy(...))->name('vacation.destroy');
    Route::get('vacation-entitlement/{date}', new VacationEntitlementController()->edit(...))->name('vacation-entitlement.edit');
    Route::post('vacation-entitlement', new VacationEntitlementController()->update(...))->name('vacation-entitlement.update');
});

Route::get('timestamp/create/{datetime}/{endDatetime?}/{type?}', new TimestampController()->create(...))->name('timestamp.create')
    ->where('endDatetime', '\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}');
Route::post('timestamp/{datetime}', new TimestampController()->store(...))->name('timestamp.store');
Route::resource('timestamp', TimestampController::class)->only(['edit', 'update', 'destroy']);
Route::patch('timestamp/{timestamp}/paid', new TimestampController()->updatePaid(...))->name('timestamp.update.paid');
Route::post('timestamp/fill', new TimestampController()->fill(...))->name('timestamp.fill');

Route::name('bug-and-feedback.')->prefix('bug-and-feedback')->group(function (): void {
    Route::get('', new BugAndFeedbackController()->index(...))->name('index');
    Route::get('export', new BugAndFeedbackController()->export(...))->name('export');
    Route::get('import', new BugAndFeedbackController()->import(...))->name('import');
    Route::delete('delete-all', new BugAndFeedbackController()->destroy(...))->name('delete-all');
});

Route::get('open', function (Request $request): void {
    if (Environment::isWindows()) {
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
