<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWelcomeRequest;
use App\Http\Resources\WorkScheduleResource;
use App\Models\Timestamp;
use App\Models\WorkSchedule;
use App\Services\WindowService;
use App\Settings\GeneralSettings;
use App\Settings\VacationSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\MenuBar;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GeneralSettings $settings, VacationSettings $vacationSettings)
    {
        $workSchedule = WorkSchedule::orderBy('valid_from')->first();

        return Inertia::render('Welcome/Index', [
            'locale' => $settings->locale,
            'openAtLogin' => App::openAtLogin(),
            'workSchedule' => $workSchedule ? WorkScheduleResource::make($workSchedule) : null,
            'vacationSettings' => [
                'default_entitlement_days' => $vacationSettings->default_entitlement_days,
                'auto_carryover' => $vacationSettings->auto_carryover,
                'minimum_day_hours' => $vacationSettings->minimum_day_hours,
            ],
        ]);
    }

    public function update(StoreWelcomeRequest $request): void
    {
        $data = $request->validated();
        if ($request->has('openAtLogin')) {
            App::openAtLogin($data['openAtLogin']);
        }

        /** @var VacationSettings $vacationSettings */
        $vacationSettings = app(VacationSettings::class);
        $shouldPersistVacation = false;
        $workScheduleForMinimum = null;

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
            $workScheduleForMinimum = $first;
            $shouldPersistVacation = true;
        }
        if ($request->has('vacation')) {
            $vacationSettings->default_entitlement_days = (float) $data['vacation']['default_entitlement_days'];
            $vacationSettings->auto_carryover = (bool) $data['vacation']['auto_carryover'];
            $shouldPersistVacation = true;
        }

        if ($shouldPersistVacation) {
            $sourceSchedule = $workScheduleForMinimum ?? WorkSchedule::orderBy('valid_from')->first();

            if ($sourceSchedule) {
                $vacationSettings->minimum_day_hours = $this->maximumHoursFromSchedule($sourceSchedule);
            }

            $vacationSettings->save();
        }
    }

    private function maximumHoursFromSchedule(WorkSchedule $workSchedule): float
    {
        return (float) Collection::make([
            $workSchedule->sunday,
            $workSchedule->monday,
            $workSchedule->tuesday,
            $workSchedule->wednesday,
            $workSchedule->thursday,
            $workSchedule->friday,
            $workSchedule->saturday,
        ])->max() ?? 0.0;
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
