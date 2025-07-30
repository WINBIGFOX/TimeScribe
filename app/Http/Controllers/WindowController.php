<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WindowService;
use App\Settings\FlyTimerSettings;

class WindowController extends Controller
{
    public function openOverview(bool $darkMode): void
    {
        WindowService::openHome($darkMode);
    }

    public function openSettings(bool $darkMode): void
    {
        WindowService::openHome($darkMode, 'settings.index');
    }

    public function openUpdater(bool $darkMode): void
    {
        WindowService::openUpdater($darkMode);
    }

    public function openNewProject(bool $darkMode): void
    {
        WindowService::openHome($darkMode, 'project.create');
    }

    public function openFlyTimer(FlyTimerSettings $flyTimerSettings): void
    {
        WindowService::openFlyTimer();
        $flyTimerSettings->showWithStart = true;
        $flyTimerSettings->save();
    }

    public function closeFlyTimer(FlyTimerSettings $flyTimerSettings): void
    {
        WindowService::closeFlyTimer();
        $flyTimerSettings->showWithStart = false;
        $flyTimerSettings->save();
    }
}
