<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\LocaleChanged;
use App\Http\Requests\StoreSettingsRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Jobs\CalculateWeekBalance;
use Inertia\Inertia;
use Native\Laravel\Facades\App;
use Native\Laravel\Facades\Settings;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/Edit', [
            'openAtLogin' => App::openAtLogin(),
            'showTimerOnUnlock' => Settings::get('showTimerOnUnlock'),
            'workdays' => Settings::get('workdays'),
            'holidayRegion' => Settings::get('holidayRegion'),
            'stopBreakAutomatic' => Settings::get('stopBreakAutomatic'),
            'stopBreakAutomaticActivationTime' => Settings::get('stopBreakAutomaticActivationTime'),
            'stopWorkTimeReset' => Settings::get('stopWorkTimeReset'),
            'stopBreakTimeReset' => Settings::get('stopBreakTimeReset'),
            'locale' => Settings::get('locale'),
            'appActivityTracking' => Settings::get('appActivityTracking'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('showTimerOnUnlock', $data['showTimerOnUnlock']);
        Settings::set('workdays', $data['workdays']);
        Settings::set('holidayRegion', $data['holidayRegion']);
        Settings::set('stopBreakAutomatic', $data['stopBreakAutomatic']);
        Settings::set('stopBreakAutomaticActivationTime', $data['stopBreakAutomaticActivationTime']);
        Settings::set('stopWorkTimeReset', (int) $data['stopWorkTimeReset']);
        Settings::set('stopBreakTimeReset', (int) $data['stopBreakTimeReset']);
        Settings::set('appActivityTracking', $data['appActivityTracking']);

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
