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
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;
use Native\Desktop\Enums\SystemThemesEnum;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\System;

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
            'defaultOverview' => $settings->default_overview,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGeneralSettingsRequest $request, GeneralSettings $settings): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $settings->showTimerOnUnlock = $data['showTimerOnUnlock'];
        $settings->holidayRegion = $data['holidayRegion'];
        $settings->appActivityTracking = $data['appActivityTracking'];
        $settings->timezone = $data['timezone'];
        $settings->default_overview = $data['default_overview'] ?? 'week';

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

        dispatch(new CalculateWeekBalance);

        return to_route('settings.general.edit');
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
