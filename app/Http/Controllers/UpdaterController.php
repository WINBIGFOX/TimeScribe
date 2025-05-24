<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WindowService;
use App\Settings\AutoUpdaterSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Native\Laravel\Facades\AutoUpdater;

class UpdaterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AutoUpdaterSettings $autoUpdaterSettings): Response
    {
        return Inertia::render('Updater/Index', [
            'auto_update' => $autoUpdaterSettings->autoUpdate,
            'last_check' => $autoUpdaterSettings->lastCheck,
            'last_version' => $autoUpdaterSettings->lastVersion,
            'is_downloaded' => $autoUpdaterSettings->isDownloaded,
        ]);
    }

    public function updateAutoUpdate(Request $request, AutoUpdaterSettings $autoUpdaterSettings): RedirectResponse
    {
        $autoUpdaterSettings->autoUpdate = $request->boolean('auto_update') ?? false;
        $autoUpdaterSettings->save();

        return redirect()->route('updater.index');
    }

    public function install(AutoUpdaterSettings $autoUpdaterSettings): void
    {
        if ($autoUpdaterSettings->isDownloaded) {
            $autoUpdaterSettings->isDownloaded = false;
            $autoUpdaterSettings->lastCheck = null;
            $autoUpdaterSettings->lastVersion = null;
            $autoUpdaterSettings->save();

            AutoUpdater::quitAndInstall();
        }

        WindowService::closeUpdater();
    }

    public function check(AutoUpdaterSettings $autoUpdaterSettings): void
    {
        if (! $autoUpdaterSettings->lastCheck instanceof \Carbon\Carbon || $autoUpdaterSettings->lastCheck->diffInHours(now()) > 1) {
            AutoUpdater::checkForUpdates();
        }
    }
}
