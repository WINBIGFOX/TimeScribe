<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TimestampService;
use App\Services\WindowService;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Native\Laravel\Facades\MenuBar;

class MenubarController extends Controller
{
    public function index()
    {
        TimestampService::ping();

        Artisan::call('menubar:refresh');

        return Inertia::render('MenuBar', [
            'currentType' => TimestampService::getCurrentType(),
            'workTime' => TimestampService::getWorkTime(),
            'breakTime' => TimestampService::getBreakTime(),
        ]);
    }

    public function storeBreak()
    {
        TimestampService::startBreak();

        return redirect()->route('menubar.index');
    }

    public function storeWork()
    {
        TimestampService::startWork();

        return redirect()->route('menubar.index');
    }

    public function storeStop()
    {
        TimestampService::stop();

        MenuBar::label('');
        MenuBar::icon(public_path('IconTemplate@2x.png'));

        return redirect()->route('menubar.index');
    }

    public function openSetting(bool $darkMode): void
    {
        WindowService::openSettings($darkMode);
    }

    public function openOverview(bool $darkMode): void
    {
        WindowService::openOverview($darkMode);
    }

    public function openAbsence(bool $darkMode): void
    {
        WindowService::openAbsence($darkMode);
    }
}
