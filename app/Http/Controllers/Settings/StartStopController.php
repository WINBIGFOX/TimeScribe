<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStartStopSettingsRequest;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class StartStopController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeneralSettings $settings)
    {
        return Inertia::render('Settings/StartStop/Edit', [
            'stopBreakAutomatic' => $settings->stopBreakAutomatic,
            'stopBreakAutomaticActivationTime' => $settings->stopBreakAutomaticActivationTime,
            'stopWorkTimeReset' => $settings->stopWorkTimeReset,
            'stopBreakTimeReset' => $settings->stopBreakTimeReset,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStartStopSettingsRequest $request, GeneralSettings $settings): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $settings->stopBreakAutomatic = $data['stopBreakAutomatic'] ?? null;
        $settings->stopBreakAutomaticActivationTime = $data['stopBreakAutomaticActivationTime'] ?? null;
        $settings->stopWorkTimeReset = ((int) $data['stopWorkTimeReset']) ?? null;
        $settings->stopBreakTimeReset = ((int) $data['stopBreakTimeReset']) ?? null;
        $settings->save();

        return to_route('settings.start-stop.edit');
    }
}
