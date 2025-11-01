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
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;
use Native\Desktop\Facades\MenuBar;

class MenubarController extends Controller
{
    public function index(Request $request, GeneralSettings $settings, AutoUpdaterSettings $autoUpdaterSettings, ProjectSettings $projectSettings): Response
    {
        $currentAppActivity = null;
        $currentType = TimestampService::getCurrentType();
        $currentProject = $projectSettings->currentProject;

        if ($currentProject) {
            $currentProject = Project::find($currentProject);
            if (! $currentProject) {
                $projectSettings->currentProject = null;
                $projectSettings->save();
            }
        }
        if (! $request->header('x-inertia-partial-data')) {
            TimestampService::ping();
            dispatch_sync(new MenubarRefresh);
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
            'currentProject' => fn () => $currentProject ? ProjectResource::make($currentProject) : null,
            'currentAppActivity' => fn () => $currentAppActivity ? ActivityHistoryResource::make($currentAppActivity) : null,
            'activeAppActivity' => $settings->appActivityTracking,
            'updateAvailable' => $autoUpdaterSettings->isDownloaded,
            'projects' => Inertia::optional(fn () => ProjectResource::collection(Project::scopes('sortedByLatestTimestamp')->get())),
        ]);
    }

    public function storeBreak(): RedirectResponse
    {
        TimestampService::startBreak();

        return to_route('menubar.index');
    }

    public function storeWork(ProjectSettings $projectSettings): RedirectResponse
    {
        TimestampService::startWork();

        return to_route('menubar.index');
    }

    public function storeStop(): RedirectResponse
    {
        TimestampService::stop();

        MenuBar::label('');
        MenuBar::icon(TrayIconService::getIcon());

        return to_route('menubar.index');
    }

    public function setProject(ProjectSettings $projectSettings, int $project): RedirectResponse
    {
        $projectSettings->currentProject = $project;
        $projectSettings->save();
        TimestampService::startWork();

        return to_route('menubar.index');
    }

    public function removeProject(ProjectSettings $projectSettings): Redirector|RedirectResponse
    {
        $projectSettings->currentProject = null;
        $projectSettings->save();

        if (TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
            TimestampService::startWork();
        }

        return to_route('menubar.index');
    }
}
