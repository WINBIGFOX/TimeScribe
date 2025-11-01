<?php

declare(strict_types=1);

namespace App\Http\Controllers\Overview;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AbsenceResource;
use App\Services\HolidayService;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $week = $date->copy()->weekOfYear;
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        CarbonPeriod::between($startOfWeek, $endOfWeek)->toArray();

        return Inertia::render('Overview/Week/Show', [
            'date' => $date->format('d.m.Y'),
            'week' => $week,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekWorkTime' => TimestampService::getWorkTime($startOfWeek, $endOfWeek),
            'weekBreakTime' => TimestampService::getBreakTime($startOfWeek, $endOfWeek),
            'weekPlan' => TimestampService::getWeekPlan($startOfWeek),
            'weekFallbackPlan' => TimestampService::getFallbackPlan($startOfWeek, $endOfWeek),
            'weekDatesWithTimestamps' => TimestampService::getDatesWithTimestamps($date->copy()->subYear()->startOfYear(), $date->copy()->addYear()->endOfYear()),
            'balance' => TimestampService::getBalance($startOfWeek),
            'lastCalendarWeek' => $date->copy()->subWeek()->weekOfYear,
            'weekdays' => collect(new DatePeriod($startOfWeek, new DateInterval('P1D'), $endOfWeek))->map(function (DateTime $date): array {
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
            })->all(),
        ]);
    }
}
