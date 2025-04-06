<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Http\Resources\AbsenceResource;
use App\Services\TimestampService;
use App\Services\WindowService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OverviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('overview.show', Carbon::now()->format('Y-m-d'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $date)
    {
        $date = Carbon::parse($date);
        $week = $date->copy()->weekOfYear;
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        CarbonPeriod::between($startOfWeek, $endOfWeek)->toArray();

        return Inertia::render('Overview/Show', [
            'date' => $date->format('Y-m-d'),
            'week' => $week,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekWorkTime' => TimestampService::getWorkTime($startOfWeek, $endOfWeek),
            'weekBreakTime' => TimestampService::getBreakTime($startOfWeek, $endOfWeek),
            'weekPlan' => TimestampService::getWeekPlan($startOfWeek),
            'weekFallbackPlan' => TimestampService::getFallbackPlan($startOfWeek, $endOfWeek),
            'weekDatesWithTimestamps' => TimestampService::getDatesWithTimestamps($date->copy()->subYear()->startOfYear(), $date->copy()->addYear()->endOfYear()),
            'holidays' => TimestampService::getHoliday(range($date->year - 5, $date->year + 5))->map(function ($holidayDate) {
                return DateHelper::toResourceArray($holidayDate);
            }),
            'balance' => TimestampService::getBalance($startOfWeek),
            'lastCalendarWeek' => $date->copy()->subWeek()->weekOfYear,
            'weekdays' => collect(new DatePeriod($startOfWeek, new DateInterval('P1D'), $endOfWeek))->map(function (\DateTime $date) {
                $date = Carbon::parse($date);

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
                ];
            })->toArray(),
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $date, bool $darkMode)
    {
        WindowService::closeDayEdit();
        WindowService::openDayEdit($date, $darkMode);
    }
}
