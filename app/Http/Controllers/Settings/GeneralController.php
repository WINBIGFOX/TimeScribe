<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Events\LocaleChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Jobs\CalculateWeekBalance;
use Inertia\Inertia;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\App;
use Native\Laravel\Facades\Settings;
use Native\Laravel\Facades\System;

class GeneralController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/General/Edit', [
            'openAtLogin' => App::openAtLogin(),
            'theme' => Settings::get('theme', SystemThemesEnum::SYSTEM->value),
            'showTimerOnUnlock' => Settings::get('showTimerOnUnlock'),
            'holidayRegion' => Settings::get('holidayRegion'),
            'locale' => Settings::get('locale'),
            'appActivityTracking' => Settings::get('appActivityTracking'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGeneralSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('showTimerOnUnlock', $data['showTimerOnUnlock']);
        Settings::set('holidayRegion', $data['holidayRegion']);
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

        return redirect()->route('settings.general.edit');
    }

    public function updateLocale(UpdateLocaleRequest $request): void
    {
        $data = $request->validated();
        if ($data['locale'] !== Settings::get('locale')) {
            Settings::set('locale', $data['locale']);
            LocaleChanged::broadcast();
        }
    }
}
