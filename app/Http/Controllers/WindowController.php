<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WindowService;

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
}
