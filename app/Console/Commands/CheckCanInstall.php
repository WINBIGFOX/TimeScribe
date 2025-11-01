<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Settings\AutoUpdaterSettings;
use Illuminate\Console\Command;
use Native\Desktop\Facades\AutoUpdater;
use Native\Desktop\Facades\Window;

class CheckCanInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-can-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (count(Window::all()) > 0) {
            return;
        }

        $autoUpdaterSettings = app(AutoUpdaterSettings::class);

        if (! $autoUpdaterSettings->autoUpdate || ! $autoUpdaterSettings->isDownloaded) {
            return;
        }

        $autoUpdaterSettings->isDownloaded = false;
        $autoUpdaterSettings->lastCheck = null;
        $autoUpdaterSettings->lastVersion = null;
        $autoUpdaterSettings->save();
        AutoUpdater::quitAndInstall();
    }
}
