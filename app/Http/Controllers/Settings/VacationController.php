<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVacationSettingsRequest;
use App\Settings\VacationSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class VacationController extends Controller
{
    public function edit(VacationSettings $settings)
    {
        return Inertia::render('Settings/Vacation/Edit', [
            'defaultEntitlementDays' => $settings->default_entitlement_days,
            'prorationStep' => $settings->proration_step,
            'autoCarryover' => $settings->auto_carryover,
            'minimumDayHours' => $settings->minimum_day_hours,
        ]);
    }

    public function update(UpdateVacationSettingsRequest $request, VacationSettings $settings): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $settings->default_entitlement_days = $data['default_entitlement_days'];
        $settings->prorate_consumption = $data['prorate_consumption'];
        $settings->proration_step = $data['proration_step'] ?? null;
        $settings->auto_carryover = $data['auto_carryover'];
        $settings->minimum_day_hours = $data['minimum_day_hours'];
        $settings->save();

        return to_route('settings.vacation.edit');
    }
}
