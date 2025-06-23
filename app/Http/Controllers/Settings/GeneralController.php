<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\HolidayRegionEnum;
use App\Events\LocaleChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Jobs\CalculateWeekBalance;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use DateTimeZone;
use Inertia\Inertia;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\App;
use Native\Laravel\Facades\System;

class GeneralController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeneralSettings $settings)
    {
        return Inertia::render('Settings/General/Edit', [
            'openAtLogin' => App::openAtLogin(),
            'theme' => $settings->theme ?? SystemThemesEnum::SYSTEM->value,
            'showTimerOnUnlock' => $settings->showTimerOnUnlock,
            'holidayRegion' => $settings->holidayRegion,
            'holidayRegions' => HolidayRegionEnum::toArray(),
            'locale' => $settings->locale,
            'appActivityTracking' => $settings->appActivityTracking,
            'timezones' => DateTimeZone::listIdentifiers(),
            'timezone' => $settings->timezone,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGeneralSettingsRequest $request, GeneralSettings $settings)
    {
        $data = $request->validated();

        $settings->showTimerOnUnlock = $data['showTimerOnUnlock'];
        $settings->holidayRegion = $data['holidayRegion'];
        $settings->appActivityTracking = $data['appActivityTracking'];
        $settings->timezone = $data['timezone'];

        if ($data['theme'] !== $settings->theme ?? SystemThemesEnum::SYSTEM->value) {
            $settings->theme = $data['theme'];
            System::theme(SystemThemesEnum::tryFrom($data['theme']));
        }

        if ($data['locale'] !== $settings->locale) {
            $settings->locale = $data['locale'];
            LocaleChanged::broadcast();
        }

        if ($data['openAtLogin'] !== App::openAtLogin()) {
            App::openAtLogin($data['openAtLogin']);
        }

        $settings->save();

        CalculateWeekBalance::dispatch();

        return redirect()->route('settings.general.edit');
    }

    public function updateLocale(UpdateLocaleRequest $request, GeneralSettings $settings, ProjectSettings $projectSettings): void
    {
        $data = $request->validated();
        if ($data['locale'] !== $settings->locale) {

            $settings->locale = $data['locale'];
            $settings->save();
            $projectSettings->defaultCurrency = null;
            $projectSettings->save();
            LocaleChanged::broadcast();
        }
    }
}
