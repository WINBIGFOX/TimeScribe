<?php

declare(strict_types=1);

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class YearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Redirector|RedirectResponse
    {
        return to_route('overview.year.show', [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carbon $date)
    {
        $breakTimes = [];
        $workTimes = [];
        $fullWorkTimes = [];
        $overtimes = [];
        $plans = [];
        $xaxis = [];
        $groups = [];
        $links = [];

        $startDate = $date->clone()->weekOfYear(1);
        $weeksInYear = $date->weeksInYear();
        $endDate = $date->clone()->weekOfYear($weeksInYear);
        $periode = CarbonPeriod::create($startDate, '1 week', $endDate);

        foreach ($periode as $rangeDate) {
            $startOfWeek = $rangeDate->clone()->startOfWeek();
            $endOfWeek = $rangeDate->clone()->endOfWeek();
            $plan = TimestampService::getWeekPlan($rangeDate);
            $workTime = TimestampService::getWorkTime($startOfWeek, $endOfWeek);
            $breakTime = TimestampService::getBreakTime($startOfWeek, $endOfWeek);

            $plans[] = $plan;
            $breakTimes[] = $breakTime;
            $fullWorkTimes[] = $workTime;
            $workTimes[] = min($workTime, $plan * 3600);
            $overtimes[] = $workTime > $plan * 3600 ? $workTime - ($plan * 3600) : 0;
            $xaxis[] = (int) $rangeDate->format('W');

            $groups[$rangeDate->format('MY')] = [
                'title' => $rangeDate->shortMonthName,
                'cols' => ($groups[$rangeDate->format('MY')]['cols'] ?? 0) + 1,
            ];
            $links[] = route('overview.week.show', ['date' => $rangeDate->format('Y-m-d')]);
        }

        if (array_sum($breakTimes) + array_sum($workTimes) + array_sum($overtimes) <= 0) {
            $breakTimes = [];
            $workTimes = [];
            $overtimes = [];
        }

        return Inertia::render('Overview/Year/Show', [
            'date' => $date->format('d.m.Y'),
            'breakTimes' => $breakTimes,
            'workTimes' => $workTimes,
            'overtimes' => $overtimes,
            'plans' => $plans,
            'xaxis' => $xaxis,
            'sumBreakTime' => array_sum($breakTimes),
            'sumWorkTime' => min(array_sum($fullWorkTimes), array_sum($plans) * 3600),
            'sumOvertime' => max(array_sum($fullWorkTimes) - array_sum($plans) * 3600, 0),
            'sumPlan' => array_sum($plans),
            'groups' => array_values($groups),
            'links' => $links,
        ]);
    }
}
