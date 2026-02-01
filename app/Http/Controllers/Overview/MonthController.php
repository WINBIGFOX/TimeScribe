<?php

declare(strict_types=1);

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class MonthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Redirector|RedirectResponse
    {
        return to_route('overview.month.show', [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carbon $date)
    {
        $hasWorkSchedule = WorkSchedule::exists();
        $breakTimes = [];
        $workTimes = [];
        $fullWorkTimes = [];
        $overtimes = [];
        $plans = [];
        $xaxis = [];
        $links = [];

        $startDate = $date->clone()->startOfMonth();
        $endDate = $date->clone()->endOfMonth();
        $periode = CarbonPeriod::create($startDate, $endDate);
        foreach ($periode as $rangeDate) {
            $plan = TimestampService::getPlan($rangeDate);
            $workTime = TimestampService::getWorkTime($rangeDate);
            $breakTime = TimestampService::getBreakTime($rangeDate);

            $plans[] = $plan;
            $breakTimes[] = $breakTime;
            $fullWorkTimes[] = $workTime;
            $workTimes[] = min($workTime, $plan * 3600);
            $overtimes[] = $workTime > $plan * 3600 && $hasWorkSchedule ? $workTime - ($plan * 3600) : 0;
            $xaxis[] = $rangeDate->format('Y-m-d');
            $links[] = route('overview.day.show', ['date' => $rangeDate->format('Y-m-d')]);
        }

        if (array_sum($breakTimes) + ($hasWorkSchedule ? (array_sum($workTimes) + array_sum($overtimes)) : array_sum($fullWorkTimes)) <= 0) {
            $breakTimes = [];
            $workTimes = [];
            $overtimes = [];
        }

        return Inertia::render('Overview/Month/Show', [
            'date' => $date->format('Y-m-d'),
            'breakTimes' => $breakTimes,
            'workTimes' => $hasWorkSchedule ? $workTimes : $fullWorkTimes,
            'overtimes' => $overtimes,
            'plans' => $plans,
            'xaxis' => $xaxis,
            'hasWorkSchedules' => $hasWorkSchedule,
            'sumBreakTime' => array_sum($breakTimes),
            'sumWorkTime' => $hasWorkSchedule ? min(array_sum($fullWorkTimes), array_sum($plans) * 3600) : array_sum($fullWorkTimes),
            'sumOvertime' => $hasWorkSchedule ? max(array_sum($fullWorkTimes) - array_sum($plans) * 3600, 0) : 0,
            'sumPlan' => array_sum($plans),
            'links' => $links,
        ]);
    }
}
