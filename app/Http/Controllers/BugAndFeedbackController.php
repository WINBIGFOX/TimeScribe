<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LocaleChanged;
use App\Jobs\CalculateWeekBalance;
use App\Services\BackupService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Native\Desktop\Dialog;
use Native\Desktop\Enums\SystemThemesEnum;
use Native\Desktop\Facades\Alert;
use Native\Desktop\Facades\System;
use Native\Desktop\Support\Environment;

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
            $backupService->create($savePath);
        } catch (\Throwable $e) {
            Log::error('Failed to create backup: '.$e->getMessage());

            return back()->withErrors(['message' => $e->getMessage()]);
        }

        if (Environment::isWindows()) {
            shell_exec('explorer "'.$savePath.'"');
        } else {
            shell_exec('open "'.$savePath.'"');
        }

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

        $settings = app(GeneralSettings::class);

        if (System::theme()->value !== $settings->theme ?? SystemThemesEnum::SYSTEM->value) {
            System::theme(SystemThemesEnum::tryFrom($settings->theme ?? SystemThemesEnum::SYSTEM));
        }

        TimestampService::checkStopTimeReset();
        dispatch(new CalculateWeekBalance);
        LocaleChanged::broadcast();

        Alert::type('info')->show(__('app.restore successful.'));

        return to_route('bug-and-feedback.index')->withErrors(['message' => __('app.restore successful.')]);
    }
}
