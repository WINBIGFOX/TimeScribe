<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AbsenceTypeEnum;
use App\Helpers\DateHelper;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Models\Absence;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('absence.show', ['date' => Carbon::now()->format('Y-m-d')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsenceRequest $request, string $date)
    {
        $data = $request->validated();

        if ($data['type'] === AbsenceTypeEnum::SICK) {
            $data['duration'] = null;
        }

        Absence::create($data);

        return redirect()->route('absence.show', ['date' => $date]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $date)
    {
        $workdaysPlan = Settings::get('workdays', []);
        $date = Carbon::parse($date);
        $startDate = $date->clone()->subMonth();
        $endDate = $date->clone()->addMonth();
        $absences = Absence::whereBetween('date', [$startDate, $endDate])->get();

        $periode = CarbonPeriod::create($startDate, $endDate);
        $holidays = TimestampService::getHoliday([$startDate->year, $endDate->year]);

        $dayOverviews = [];
        foreach ($periode as $rangeDate) {
            $plan = $workdaysPlan[strtolower($rangeDate->format('l'))] ?? 0;

            $workTime = TimestampService::getWorkTime($rangeDate);
            $breakTime = TimestampService::getBreakTime($rangeDate);
            $noWorkTime = TimestampService::getNoWorkTime($rangeDate);

            if (! $workTime && ! $breakTime && ! $noWorkTime) {
                continue;
            }

            $isAbsence = $absences->firstWhere('date', $rangeDate->format('Y-m-d 00:00:00'));
            $isHoliday = $holidays->search($rangeDate->format('Y-m-d 00:00:00'));

            if ($isAbsence || $isHoliday) {
                $workTime = max($workTime - $plan * 3600, 0);
                $plan = 0;
            }

            if (! $workTime) {
                continue;
            }

            $dayOverviews[$rangeDate->format('Y-m-d')] = [
                'planTime' => $plan * 3600,
                'workTime' => $workTime,
                'breakTime' => $breakTime,
                'noWorkTime' => $noWorkTime,
            ];
        }

        return Inertia::render('Absence/Show', [
            'dayOverviews' => $dayOverviews,
            'absences' => AbsenceResource::collection($absences),
            'holidays' => $holidays->map(function ($holidayDate) {
                return DateHelper::toResourceArray($holidayDate);
            }),
            'workdaysPlan' => $workdaysPlan,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $date, Absence $absence)
    {
        $absence->delete();

        return redirect()->route('absence.show', ['date' => $date]);
    }
}
