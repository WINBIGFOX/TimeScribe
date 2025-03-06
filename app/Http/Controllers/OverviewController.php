<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Http\Resources\AbsenceResource;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Native\Laravel\Facades\Window;

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
            'weekPlan' => TimestampService::getWeekPlan(),
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
                    'plan' => TimestampService::getPlan(strtolower($date->format('l'))),
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
        Window::close('day-edit');

        Window::open('day-edit')
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->route('day.edit', ['date' => $date])
            ->width(850)
            ->height(415)
            ->minWidth(700)
            ->titleBarHidden()
            ->resizable(true)
            ->backgroundColor($darkMode ? '#020817' : '#ffffff')
            ->fullscreenable(false)
            ->showDevTools(false);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
