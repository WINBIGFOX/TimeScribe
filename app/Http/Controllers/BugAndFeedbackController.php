<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LocaleChanged;
use App\Jobs\CalculateWeekBalance;
use App\Services\BackupService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Native\Desktop\Dialog;
use Native\Desktop\Enums\SystemThemesEnum;
use Native\Desktop\Facades\Alert;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\Shell;
use Native\Desktop\Facades\System;
use Throwable;

class BugAndFeedbackController extends Controller
{
    public function index()
    {
        return Inertia::render('BugAndFeedback/Index');
    }

    public function export()
    {
        $savePath = Dialog::new()->asSheet()
            ->folders()
            ->button(__('app.create backup'))
            ->open();

        if ($savePath === null) {
            return back();
        }

        $backupService = new BackupService;

        if ($backupService->backupFileExists($savePath)) {
            $allowOverride = Alert::buttons([
                __('app.yes'),
                __('app.cancel'),
            ])
                ->defaultId(0)
                ->cancelId(1)
                ->title(__('app.warning'))
                ->show(__('app.backup already exists. Do you want to overwrite it?'));

            if ($allowOverride === 1) {
                return back()->withErrors(['message' => __('app.backup was cancelled.')]);
            }
        }

        try {
            $backupPath = $backupService->create($savePath);
        } catch (\Throwable $e) {
            Log::error('Failed to create backup: '.$e->getMessage());

            return back()->withErrors(['message' => $e->getMessage()]);
        }

        Shell::showInFolder($backupPath);

        return back()->withErrors(['message' => __('app.backup successfully created.')]);
    }

    public function import()
    {
        $backupFilePath = Dialog::new()->asSheet()
            ->filter('TimeScribe Backup', ['bak'])
            ->files()
            ->button(__('app.restoring'))
            ->open();

        if ($backupFilePath === null) {
            return back();
        }

        try {
            (new BackupService)->restore($backupFilePath);
        } catch (\Throwable $e) {
            Log::error('Failed to open zip file: '.$backupFilePath);
            Alert::error(__('app.restoring'), $e->getMessage());

            return back()->withErrors(['message' => $e->getMessage()]);
        }

        $settings = resolve(GeneralSettings::class);

        if (System::theme()->value !== $settings->theme ?? SystemThemesEnum::SYSTEM->value) {
            System::theme(SystemThemesEnum::tryFrom($settings->theme ?? SystemThemesEnum::SYSTEM));
        }

        TimestampService::checkStopTimeReset();
        dispatch(new CalculateWeekBalance);
        LocaleChanged::broadcast();

        Alert::type('info')->show(__('app.restore successful.'));

        return to_route('bug-and-feedback.index')->withErrors(['message' => __('app.restore successful.')]);
    }

    public function destroy(): RedirectResponse
    {
        try {
            BackupService::dropExistingTablesAndViews();
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('native:migrate', ['--force' => true]);
            Artisan::call('db:optimize');
            Cache::flush();

            $this->clearDiskRoot('local');
            $this->clearDiskRoot('public');
            $this->clearDiskRoot('app-icon');

            $this->cleanStoragePath('app/backup-temp');
            $this->cleanStoragePath('backup');
            $this->cleanStoragePath('app_icons');
            $this->cleanStoragePath('app_icns');
            $this->cleanStoragePath('logs');
            $this->cleanStoragePath('testing');
            $this->cleanStoragePath('framework/cache');
            $this->cleanStoragePath('framework/cache/data');

            Alert::new()->type('info')->show(__('app.all data deleted successfully'));
            App::relaunch();

            return back();
        } catch (Throwable $throwable) {
            Log::error('Failed to delete all data: '.$throwable->getMessage());
            Alert::error(__('app.an error occurred while deleting all data.'), $throwable->getMessage());
            App::relaunch();

            return back();
        }
    }

    private function clearDiskRoot(string $disk): void
    {
        $root = Storage::disk($disk)->path('/');
        if (! File::isDirectory($root)) {
            File::cleanDirectory($root);
        }
    }

    private function cleanStoragePath(string $relativePath): void
    {
        $path = storage_path($relativePath);
        if (! File::isDirectory($path)) {
            File::cleanDirectory($path);
        }
    }
}
