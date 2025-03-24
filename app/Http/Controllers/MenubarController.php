<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ActivityHistoryResource;
use App\Models\ActivityHistory;
use App\Services\TimestampService;
use App\Services\WindowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Settings;

class MenubarController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->header('x-inertia-partial-data')) {
            TimestampService::ping();
            Artisan::call('menubar:refresh');
            if (Settings::get('appActivityTracking', false)) {
                Artisan::call('app:active-app');
            }
        }

        $currentAppActivity = ActivityHistory::active()->latest()->first();

        return Inertia::render('MenuBar', [
            'currentType' => TimestampService::getCurrentType(),
            'workTime' => TimestampService::getWorkTime(),
            'breakTime' => TimestampService::getBreakTime(),
            'currentAppActivity' => fn () => $currentAppActivity ? ActivityHistoryResource::make($currentAppActivity) : null,
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
