<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingsRequest;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/Edit', [
            'startOnLogin' => Settings::get('startOnLogin'),
            'showTimerOnUnlock' => Settings::get('showTimerOnUnlock'),
            'workdays' => Settings::get('workdays'),
            'holidayRegion' => Settings::get('holidayRegion'),
            'stopBreakAutomatic' => Settings::get('stopBreakAutomatic'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('startOnLogin', $data['startOnLogin']);
        Settings::set('showTimerOnUnlock', $data['showTimerOnUnlock']);
        Settings::set('workdays', $data['workdays']);
        Settings::set('holidayRegion', $data['holidayRegion']);
        Settings::set('stopBreakAutomatic', $data['stopBreakAutomatic']);

        return redirect()->route('settings.edit');
    }
}
