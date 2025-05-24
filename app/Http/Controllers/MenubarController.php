<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TimestampTypeEnum;
use App\Http\Resources\ActivityHistoryResource;
use App\Jobs\MenubarRefresh;
use App\Models\ActivityHistory;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use App\Settings\AutoUpdaterSettings;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;
use Native\Laravel\Facades\MenuBar;

class MenubarController extends Controller
{
    public function index(Request $request, GeneralSettings $settings, AutoUpdaterSettings $autoUpdaterSettings): Response
    {
        $currentAppActivity = null;
        $currentType = TimestampService::getCurrentType();
        if (! $request->header('x-inertia-partial-data')) {
            TimestampService::ping();
            MenubarRefresh::dispatchSync();
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
            'updateAvailable' => $autoUpdaterSettings->isDownloaded,
        ]);
    }

    public function storeBreak(): RedirectResponse
    {
        TimestampService::startBreak();

        return redirect()->route('menubar.index');
    }

    public function storeWork(): RedirectResponse
    {
        TimestampService::startWork();

        return redirect()->route('menubar.index');
    }

    public function storeStop(): RedirectResponse
    {
        TimestampService::stop();

        MenuBar::label('');
        MenuBar::icon(TrayIconService::getIcon());

        return redirect()->route('menubar.index');
    }

    public function resize(Request $request): void
    {
        MenuBar::resize(300, $request->integer('height', 298));
    }
}
