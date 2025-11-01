<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use App\Settings\ProjectSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Native\Desktop\Facades\MenuBar;

class FlyTimerController extends Controller
{
    public function index(ProjectSettings $projectSettings): Response
    {

        $currentProject = $projectSettings->currentProject;
        if ($currentProject) {
            $currentProject = Project::find($currentProject);
            if (! $currentProject) {
                $projectSettings->currentProject = null;
                $projectSettings->save();
            }
        }

        return Inertia::render('FlyTimer/Index', [
            'workTime' => TimestampService::getWorkTime(),
            'breakTime' => TimestampService::getBreakTime(),
            'currentType' => TimestampService::getCurrentType(),
            'currentProject' => fn () => $currentProject ? ProjectResource::make($currentProject) : null,
        ]);
    }

    public function storeBreak(): RedirectResponse
    {
        TimestampService::startBreak();

        return to_route('fly-timer.index');
    }

    public function storeWork(ProjectSettings $projectSettings): RedirectResponse
    {
        TimestampService::startWork();

        return to_route('fly-timer.index');
    }

    public function storeStop(): RedirectResponse
    {
        TimestampService::stop();

        MenuBar::label('');
        MenuBar::icon(TrayIconService::getIcon());

        return to_route('fly-timer.index');
    }
}
