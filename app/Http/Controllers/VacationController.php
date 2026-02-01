<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AbsenceTypeEnum;
use App\Http\Requests\DestroyVacationRequest;
use App\Http\Resources\VacationAbsenceResource;
use App\Models\Absence;
use App\Models\VacationEntitlement;
use App\Models\WorkSchedule;
use App\Services\VacationContextService;
use App\Settings\VacationSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class VacationController extends Controller
{
    public function index(?string $date = null): Response
    {
        $referenceDate = $date ? Date::parse($date) : Date::now();
        $selectedYear = $referenceDate->year;

        $settings = resolve(VacationSettings::class);
        $overrides = VacationEntitlement::query()->get()->keyBy('year');

        $vacations = Absence::query()
            ->where('type', AbsenceTypeEnum::VACATION)
            ->whereYear('date', $selectedYear)
            ->orderByDesc('date')
            ->get();

        $vacationContextService = resolve(VacationContextService::class);

        $availableYears = $vacationContextService->availableYears($selectedYear, $overrides);
        $minYear = min($availableYears);

        $context = $vacationContextService->yearContext($selectedYear, $overrides, $minYear, $vacations, Date::now());

        $override = $overrides->get($selectedYear);
        $hasWorkSchedule = WorkSchedule::exists();

        return Inertia::render('Absence/Vacation/Index', [
            'date' => $referenceDate->format('Y-m-d'),
            'summary' => [
                'taken' => $context['summary']['taken'],
                'planned' => $context['summary']['planned'],
                'consumed' => $context['summary']['total'],
                'totalEntitlement' => $context['total_entitlement'],
                'remaining' => $context['remaining'],
            ],
            'entitlement' => [
                'year' => $selectedYear,
                'days' => $override?->days,
                'carryover' => $override?->carryover,
                'defaultDays' => $settings->default_entitlement_days,
                'autoCarryover' => $settings->auto_carryover,
            ],
            'entries' => VacationAbsenceResource::collection($context['vacations']),
            'availableYears' => $availableYears,
            'hasWorkSchedule' => $hasWorkSchedule,
        ]);
    }

    public function destroy(DestroyVacationRequest $request, Absence $absence): Redirector|RedirectResponse
    {
        $request->validated();

        $date = $absence->date->format('Y-m-d');
        $absence->delete();

        return to_route('absence.vacation.index', ['date' => $date]);
    }
}
