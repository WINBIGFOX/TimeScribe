<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LocaleChanged;
use App\Jobs\CalculateWeekBalance;
use App\Services\TimestampService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Native\Laravel\Dialog;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\Alert;
use Native\Laravel\Facades\Settings;
use Native\Laravel\Facades\System;
use ZipArchive;

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

        $zip = new ZipArchive;

        if (is_file($savePath.'/TimeScribe-Backup.bac')) {
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

        if ($zip->open($savePath.'/TimeScribe-Backup.bac', ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $filesToZip = [
                storage_path('../database/database.sqlite'),
                storage_path('../config.json'),
            ];

            foreach ($filesToZip as $file) {
                if (! is_file($file)) {
                    Log::error('File not found: '.$file);

                    return back()->withErrors(['message' => __('app.backup could not be created.')]);
                }
                $zip->addFile($file, basename($file));
            }

            $logFiles = scandir(storage_path('logs'));
            foreach ($logFiles as $logFile) {
                if (is_file(storage_path('logs/'.$logFile))) {
                    $zip->addFile(storage_path('logs/'.$logFile), 'logs/'.$logFile);
                }
            }

            $icons = scandir(storage_path('app_icons'));
            foreach ($icons as $icon) {
                if (is_file(storage_path('app_icons/'.$icon))) {
                    $zip->addFile(storage_path('app_icons/'.$icon), 'app_icons/'.$icon);
                }
            }

            $zip->close();
        } else {
            Log::error('Failed to create zip file: '.$savePath.'/TimeScribe-Backup.bac');

            return back()->withErrors(['message' => __('app.backup could not be created.')]);
        }

        shell_exec('open "'.$savePath.'"');

        return back()->withErrors(['message' => __('app.backup successfully created.')]);
    }

    public function import()
    {
        $backupFilePath = Dialog::new()->asSheet()
            ->filter('TimeScribe Backup', ['bac'])
            ->files()
            ->button(__('app.restoring'))
            ->open();

        if ($backupFilePath === null) {
            return back();
        }

        $zip = new ZipArchive;

        if ($zip->open($backupFilePath) === true) {
            $nbFile = $zip->numFiles;
            for ($i = 0; $i < $nbFile; $i++) {
                if ($zip->getNameIndex($i) === 'database.sqlite') {
                    \DB::disconnect();
                    \DB::purge();
                    File::delete(storage_path('../database/database.sqlite-shm'));
                    File::delete(storage_path('../database/database.sqlite-wal'));
                    $zip->extractTo(storage_path('../database/'), ['database.sqlite']);
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('native:migrate', ['--force' => true]);
                    \DB::reconnect();
                } elseif (str_contains($zip->getNameIndex($i), 'app_icons/') || str_contains($zip->getNameIndex($i), 'logs/')) {
                    $zip->extractTo(storage_path(), [$zip->getNameIndex($i)]);
                } elseif ($zip->getNameIndex($i) === 'config.json') {
                    $zip->extractTo(storage_path('../'), ['config.json']);
                }
            }
            Cache::flush();
            $zip->close();
        } else {
            Log::error('Failed to open zip file: '.$backupFilePath);
            Alert::error(__('app.restoring'), __('app.restore failed.'));

            return back()->withErrors(['message' => __('app.restore failed.')]);
        }

        if (System::theme()->value !== Settings::get('theme', SystemThemesEnum::SYSTEM->value)) {
            System::theme(SystemThemesEnum::tryFrom(Settings::get('theme', SystemThemesEnum::SYSTEM)));
        }

        TimestampService::checkStopTimeReset();
        CalculateWeekBalance::dispatch();
        LocaleChanged::broadcast();

        Alert::type('info')->show(__('app.restore successful.'));

        return redirect()->route('bug-and-feedback.index')->withErrors(['message' => __('app.restore successful.')]);
    }
}
