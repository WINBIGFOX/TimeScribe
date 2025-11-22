<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVacationEntitlementRequest;
use App\Models\VacationEntitlement;
use App\Services\VacationContextService;
use App\Settings\VacationSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class VacationEntitlementController extends Controller
{
    public function edit(Carbon $date)
    {
        $settings = app(VacationSettings::class);

        $year = $date->year;

        $overrides = VacationEntitlement::query()->get()->keyBy('year');

        $entitlement = $overrides->get($year);

        $vacationContextService = app(VacationContextService::class);

        $minYear = $vacationContextService->minYear($year, $overrides);

        $calculatedCarryover = null;
        if ($settings->auto_carryover) {
            $calculatedCarryover = $vacationContextService->calculatedCarryover($year, $overrides, $minYear);
        }

        return Inertia::modal('Absence/Vacation/Entitlement/Edit', [
            'submit_route' => route('absence.vacation-entitlement.update'),
            'entitlement' => [
                'year' => $year,
                'days' => $entitlement?->days,
                'carryover' => $entitlement?->carryover,
                'defaultDays' => $settings->default_entitlement_days,
                'autoCarryover' => $settings->auto_carryover,
                'calculatedCarryover' => $calculatedCarryover,
            ],
        ])->baseRoute('absence.vacation.index', ['date' => $date->format('Y-m-d')]);
    }

    public function update(UpdateVacationEntitlementRequest $request): Redirector|RedirectResponse
    {
        $data = $request->validated();

        if ($data['days'] === null && $data['carryover'] === null) {
            VacationEntitlement::query()->where('year', $data['year'])->delete();

            $date = Date::create((int) $data['year'], 1, 1);

            return to_route('absence.vacation.index', ['date' => $date->format('Y-m-d')]);
        }

        $entitlement = VacationEntitlement::updateOrCreate(
            ['year' => $data['year']],
            [
                'days' => $data['days'],
                'carryover' => $data['carryover'],
            ]
        );

        $date = Date::create((int) $entitlement->year, 1, 1);

        return to_route('absence.vacation.index', ['date' => $date->format('Y-m-d')]);
    }
}
