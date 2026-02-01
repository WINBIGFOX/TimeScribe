<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AbsenceTypeEnum;
use App\Helpers\DateHelper;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Models\Absence;
use App\Models\WorkSchedule;
use App\Services\HolidayService;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Redirector|RedirectResponse
    {
        return to_route('absence.show', ['date' => Date::now()->format('Y-m-d')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsenceRequest $request, Carbon $date): Redirector|RedirectResponse
    {
        $data = $request->validated();

        if ($data['type'] === AbsenceTypeEnum::SICK) {
            $data['duration'] = null;
        }

        Absence::create($data);

        return to_route('absence.show', ['date' => $date->format('Y-m-d')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carbon $date)
    {
        $startDate = $date->clone()->startOfMonth()->subWeek();
        $endDate = $date->clone()->endOfMonth()->addWeeks(2);
        $absences = Absence::query()->whereBetween('date', [$startDate, $endDate])->get();

        $periode = CarbonPeriod::create($startDate, $endDate);
        $holidays = HolidayService::getHoliday([$startDate->year, $endDate->year]);
        $plans = [];
        foreach ($periode as $rangeDate) {
            $plans[$rangeDate->format('Y-m-d')] = TimestampService::getPlan($rangeDate);
        }

        return Inertia::render('Absence/Show', [
            'absences' => AbsenceResource::collection($absences),
            'plans' => $plans,
            'hasWorkSchedules' => WorkSchedule::exists(),
            'holidays' => $holidays->map(fn (?Carbon $holidayDate): ?array => DateHelper::toResourceArray($holidayDate)),
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carbon $date, Absence $absence): Redirector|RedirectResponse
    {
        $absence->delete();

        return to_route('absence.show', ['date' => $date->format('Y-m-d')]);
    }
}
