<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStartStopSettingsRequest;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

class StartStopController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/StartStop/Edit', [
            'stopBreakAutomatic' => Settings::get('stopBreakAutomatic'),
            'stopBreakAutomaticActivationTime' => Settings::get('stopBreakAutomaticActivationTime'),
            'stopWorkTimeReset' => Settings::get('stopWorkTimeReset'),
            'stopBreakTimeReset' => Settings::get('stopBreakTimeReset'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStartStopSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('stopBreakAutomatic', $data['stopBreakAutomatic']);
        Settings::set('stopBreakAutomaticActivationTime', $data['stopBreakAutomaticActivationTime']);
        Settings::set('stopWorkTimeReset', (int) $data['stopWorkTimeReset']);
        Settings::set('stopBreakTimeReset', (int) $data['stopBreakTimeReset']);

        return redirect()->route('settings.start-stop.edit');
    }
}
