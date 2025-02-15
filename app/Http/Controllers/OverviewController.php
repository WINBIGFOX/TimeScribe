<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
            'weekdays' => [
                'monday' => [
                    'plan' => TimestampService::getPlan('monday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek),
                    'date' => DateHelper::toResourceArray($startOfWeek),
                    'workTime' => TimestampService::getWorkTime($startOfWeek),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek),
                ],
                'tuesday' => [
                    'plan' => TimestampService::getPlan('tuesday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDay()),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDay()),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDay()),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDay()),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDay()),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDay()),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDay()),
                ],
                'wednesday' => [
                    'plan' => TimestampService::getPlan('wednesday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDays(2)),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(2)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(2)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(2)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(2)),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDays(2)),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDays(2)),
                ],
                'thursday' => [
                    'plan' => TimestampService::getPlan('thursday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDays(3)),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(3)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(3)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(3)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(3)),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDays(3)),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDays(3)),
                ],
                'friday' => [
                    'plan' => TimestampService::getPlan('friday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDays(4)),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(4)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(4)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(4)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(4)),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDays(4)),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDays(4)),
                ],
                'saturday' => [
                    'plan' => TimestampService::getPlan('saturday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDays(5)),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(5)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(5)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(5)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(5)),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDays(5)),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDays(5)),
                ],
                'sunday' => [
                    'plan' => TimestampService::getPlan('sunday'),
                    'fallbackPlan' => TimestampService::getFallbackPlan($startOfWeek->copy()->addDays(6)),
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(6)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(6)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(6)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(6)),
                    'noWorkTime' => TimestampService::getNoWorkTime($startOfWeek->copy()->addDays(6)),
                    'activeWork' => TimestampService::getActiveWork($startOfWeek->copy()->addDays(6)),
                ],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $date)
    {
        Window::close('day-edit');

        Window::open('day-edit')
            ->rememberState()
            ->alwaysOnTop()
            ->maximizable(false)
            ->fullscreen(false)
            ->route('day.edit', ['date' => $date])
            ->width(850)
            ->height(415)
            ->minWidth(700)
            ->titleBarHidden()
            ->resizable(true)
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
