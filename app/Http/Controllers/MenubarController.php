<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TimestampTypeEnum;
use App\Http\Resources\ActivityHistoryResource;
use App\Models\ActivityHistory;
use App\Services\TimestampService;
use App\Services\WindowService;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Native\Laravel\Facades\MenuBar;

class MenubarController extends Controller
{
    public function index(Request $request, GeneralSettings $settings)
    {
        $currentAppActivity = null;
        $currentType = TimestampService::getCurrentType();
        if (! $request->header('x-inertia-partial-data')) {
            TimestampService::ping();
            Artisan::call('menubar:refresh');
            if ($settings->appActivityTracking && $currentType === TimestampTypeEnum::WORK) {
                Artisan::call('app:active-app');
            }
        }

        if ($settings->appActivityTracking && $currentType === TimestampTypeEnum::WORK) {
            $currentAppActivity = ActivityHistory::active()->latest()->first();
        }

        return Inertia::render('MenuBar', [
            'currentType' => $currentType,
            'workTime' => TimestampService::getWorkTime(),
            'breakTime' => TimestampService::getBreakTime(),
            'currentAppActivity' => fn () => $currentAppActivity ? ActivityHistoryResource::make($currentAppActivity) : null,
            'activeAppActivity' => $settings->appActivityTracking,
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
        WindowService::openHome($darkMode, 'settings.index');
    }

    public function openOverview(bool $darkMode): void
    {
        WindowService::openHome($darkMode);
    }
}
