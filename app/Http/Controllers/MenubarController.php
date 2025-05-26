<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TimestampTypeEnum;
use App\Http\Resources\ActivityHistoryResource;
use App\Http\Resources\ProjectResource;
use App\Jobs\MenubarRefresh;
use App\Models\ActivityHistory;
use App\Models\Project;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use App\Settings\AutoUpdaterSettings;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;
use Native\Laravel\Facades\MenuBar;

class MenubarController extends Controller
{
    public function index(Request $request, GeneralSettings $settings, AutoUpdaterSettings $autoUpdaterSettings, ProjectSettings $projectSettings): Response
    {
        $currentAppActivity = null;
        $currentType = TimestampService::getCurrentType();
        $currentProject = $projectSettings->currentProject;
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
            'currentProject' => fn () => $currentProject ? ProjectResource::make(Project::find($currentProject)) : null,
            'currentAppActivity' => fn () => $currentAppActivity ? ActivityHistoryResource::make($currentAppActivity) : null,
            'activeAppActivity' => $settings->appActivityTracking,
            'updateAvailable' => $autoUpdaterSettings->isDownloaded,
            'projects' => Inertia::optional(fn () => ProjectResource::collection(Project::with('children')->get())),
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

    public function setProject(ProjectSettings $projectSettings, int $project): RedirectResponse
    {
        MenuBar::resize(300, 298);
        $projectSettings->currentProject = $project;
        $projectSettings->save();
        TimestampService::startWork();

        return redirect()->route('menubar.index');
    }

    public function removeProject(ProjectSettings $projectSettings)
    {
        $projectSettings->currentProject = null;
        $projectSettings->save();
        MenuBar::resize(300, 250);

        return redirect()->route('menubar.index');
    }
}
