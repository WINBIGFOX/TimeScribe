<?php

declare(strict_types=1);

namespace App\Http\Controllers\Overview;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AbsenceResource;
use App\Models\WorkSchedule;
use App\Services\HolidayService;
use App\Services\TimestampService;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class WeekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Redirector|RedirectResponse
    {
        return to_route('overview.week.show', [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carbon $date)
    {
        $hasWorkSchedules = WorkSchedule::exists();
        $week = $date->copy()->weekOfYear;
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $weekDays = collect(new DatePeriod($startOfWeek, new DateInterval('P1D'), $endOfWeek))->map(function (DateTime $date): array {
            $date = Date::parse($date);

            return [
                'plan' => TimestampService::getPlan($date),
                'fallbackPlan' => TimestampService::getFallbackPlan($date),
                'date' => DateHelper::toResourceArray($date),
                'workTime' => TimestampService::getWorkTime($date),
                'breakTime' => TimestampService::getBreakTime($date),
                'timestamps' => TimestampService::getTimestamps($date),
                'noWorkTime' => TimestampService::getNoWorkTime($date),
                'activeWork' => TimestampService::getActiveWork($date),
                'absences' => AbsenceResource::collection(TimestampService::getAbsence($date)),
                'isHoliday' => HolidayService::isHoliday($date),
            ];
        });

        $weekPlan = TimestampService::getWeekPlan($startOfWeek);

        if (! $hasWorkSchedules) {
            $maxWorkTime = $weekDays->max('workTime');
            $weekDays = $weekDays->map(function (array $day) use ($maxWorkTime): array {
                if (($day['timestamps'])->isNotEmpty()) {
                    $day['plan'] = $maxWorkTime ? $maxWorkTime / 3600 : 10;
                }

                return $day;
            });
            $weekPlan = $weekDays->sum('workTime') / 3600;
        }

        return Inertia::render('Overview/Week/Show', [
            'date' => $date->format('Y-m-d'),
            'week' => $week,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekWorkTime' => TimestampService::getWorkTime($startOfWeek, $endOfWeek),
            'weekBreakTime' => TimestampService::getBreakTime($startOfWeek, $endOfWeek),
            'weekPlan' => $weekPlan,
            'weekFallbackPlan' => TimestampService::getFallbackPlan($startOfWeek, $endOfWeek),
            'weekDatesWithTimestamps' => TimestampService::getDatesWithTimestamps($date->copy()->subYear()->startOfYear(), $date->copy()->addYear()->endOfYear()),
            'start_balance' => TimestampService::getBalance($startOfWeek),
            'lastCalendarWeek' => $date->copy()->subWeek()->weekOfYear,
            'hasWorkSchedules' => $hasWorkSchedules,
            'weekdays' => $weekDays->all(),
        ]);
    }
}
