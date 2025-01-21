<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Services\TimestampService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

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
        $workdays = Settings::get('workdays', []);

        return Inertia::render('Overview/Show', [
            'date' => $date->format('Y-m-d'),
            'week' => $week,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekdays' => [
                'monday' => [
                    'plan' => $workdays['monday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek),
                    'workTime' => TimestampService::getWorkTime($startOfWeek),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek),
                ],
                'tuesday' => [
                    'plan' => $workdays['tuesday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDay()),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDay()),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDay()),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDay()),
                ],
                'wednesday' => [
                    'plan' => $workdays['wednesday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(2)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(2)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(2)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(2)),
                ],
                'thursday' => [
                    'plan' => $workdays['thursday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(3)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(3)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(3)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(3)),
                ],
                'friday' => [
                    'plan' => $workdays['friday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(4)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(4)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(4)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(4)),
                ],
                'saturday' => [
                    'plan' => $workdays['saturday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(5)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(5)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(5)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(5)),
                ],
                'sunday' => [
                    'plan' => $workdays['sunday'] ?? 0,
                    'date' => DateHelper::toResourceArray($startOfWeek->copy()->addDays(6)),
                    'workTime' => TimestampService::getWorkTime($startOfWeek->copy()->addDays(6)),
                    'breakTime' => TimestampService::getBreakTime($startOfWeek->copy()->addDays(6)),
                    'timestamps' => TimestampService::getTimestamps($startOfWeek->copy()->addDays(6)),
                ],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
