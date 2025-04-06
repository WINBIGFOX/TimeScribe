<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LocaleChanged;
use App\Http\Requests\StoreSettingsRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Http\Resources\WorkScheduleResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\WorkSchedule;
use Inertia\Inertia;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\App;
use Native\Laravel\Facades\Settings;
use Native\Laravel\Facades\System;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/Edit', [
            'openAtLogin' => App::openAtLogin(),
            'theme' => Settings::get('theme', SystemThemesEnum::SYSTEM->value),
            'showTimerOnUnlock' => Settings::get('showTimerOnUnlock'),
            'holidayRegion' => Settings::get('holidayRegion'),
            'stopBreakAutomatic' => Settings::get('stopBreakAutomatic'),
            'stopBreakAutomaticActivationTime' => Settings::get('stopBreakAutomaticActivationTime'),
            'stopWorkTimeReset' => Settings::get('stopWorkTimeReset'),
            'stopBreakTimeReset' => Settings::get('stopBreakTimeReset'),
            'locale' => Settings::get('locale'),
            'appActivityTracking' => Settings::get('appActivityTracking'),
            'workSchedules' => WorkScheduleResource::collection(WorkSchedule::orderByDesc('valid_from')->get()->append(['is_current'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('showTimerOnUnlock', $data['showTimerOnUnlock']);
        Settings::set('holidayRegion', $data['holidayRegion']);
        Settings::set('stopBreakAutomatic', $data['stopBreakAutomatic']);
        Settings::set('stopBreakAutomaticActivationTime', $data['stopBreakAutomaticActivationTime']);
        Settings::set('stopWorkTimeReset', (int) $data['stopWorkTimeReset']);
        Settings::set('stopBreakTimeReset', (int) $data['stopBreakTimeReset']);
        Settings::set('appActivityTracking', $data['appActivityTracking']);

        if ($data['theme'] !== Settings::get('theme', SystemThemesEnum::SYSTEM->value)) {
            Settings::set('theme', $data['theme']);
            System::theme(SystemThemesEnum::tryFrom($data['theme']));
        }

        if ($data['locale'] !== Settings::get('locale')) {
            Settings::set('locale', $data['locale']);
            LocaleChanged::broadcast();
        }

        if ($data['openAtLogin'] !== App::openAtLogin()) {
            App::openAtLogin($data['openAtLogin']);
        }

        CalculateWeekBalance::dispatch();

        return redirect()->route('settings.edit');
    }

    public function updateLocale(UpdateLocaleRequest $request)
    {
        $data = $request->validated();
        if ($data['locale'] !== Settings::get('locale')) {
            Settings::set('locale', $data['locale']);
            LocaleChanged::broadcast();
        }
    }
}
