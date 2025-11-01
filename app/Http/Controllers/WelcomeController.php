<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWelcomeRequest;
use App\Http\Resources\WorkScheduleResource;
use App\Models\Timestamp;
use App\Models\WorkSchedule;
use App\Services\WindowService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\MenuBar;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GeneralSettings $settings)
    {
        $workSchedule = WorkSchedule::orderBy('valid_from')->first();

        return Inertia::render('Welcome/Index', [
            'locale' => $settings->locale,
            'openAtLogin' => App::openAtLogin(),
            'workSchedule' => $workSchedule ? WorkScheduleResource::make($workSchedule) : null,
        ]);
    }

    public function update(StoreWelcomeRequest $request): void
    {
        $data = $request->validated();
        if ($request->has('openAtLogin')) {
            App::openAtLogin($data['openAtLogin']);
        }
        if ($request->has('workSchedule')) {
            $firstTimestamps = Timestamp::orderBy('started_at')->first();
            $first = WorkSchedule::orderBy('valid_from')->firstOrNew();
            $first->valid_from = $firstTimestamps?->started_at->startOfDay() ?? today();
            $first->sunday = $data['workSchedule']['sunday'] ?? 0;
            $first->monday = $data['workSchedule']['monday'] ?? 0;
            $first->tuesday = $data['workSchedule']['tuesday'] ?? 0;
            $first->wednesday = $data['workSchedule']['wednesday'] ?? 0;
            $first->thursday = $data['workSchedule']['thursday'] ?? 0;
            $first->friday = $data['workSchedule']['friday'] ?? 0;
            $first->saturday = $data['workSchedule']['saturday'] ?? 0;
            $first->save();
        }
    }

    public function finish(GeneralSettings $settings, $openSettings = false): void
    {
        $settings->showTimerOnUnlock = true;
        $settings->wizard_completed = true;
        $settings->save();
        WindowService::closeWelcome();
        if ($openSettings) {
            WindowService::openHome(false, 'settings.index');
        } else {
            Sleep::sleep(1);
            MenuBar::show();
        }
    }
}
