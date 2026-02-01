<?php

declare(strict_types=1);

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimestampResource;
use App\Models\WorkSchedule;
use App\Services\HolidayService;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class DayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Redirector|RedirectResponse
    {
        return to_route('overview.day.show', [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Carbon $date)
    {
        $startDay = $date->copy()->startOfDay();
        $endDay = $date->copy()->endOfDay();

        return Inertia::render('Overview/Day/Show', [
            'timestamps' => TimestampResource::collection(TimestampService::getTimestamps(date: $startDay, endDate: $endDay, with: ['project'])),
            'dayWorkTime' => TimestampService::getWorkTime($startDay, $endDay),
            'dayBreakTime' => TimestampService::getBreakTime($startDay, $endDay),
            'dayPlan' => TimestampService::getPlan($date),
            'dayFallbackPlan' => TimestampService::getFallbackPlan($startDay, $endDay),
            'dayNoWorkTime' => TimestampService::getNoWorkTime($startDay),
            'absences' => TimestampService::getAbsence($startDay),
            'date' => $date->format('Y-m-d'),
            'isHoliday' => HolidayService::isHoliday($startDay),
            'hasWorkSchedules' => WorkSchedule::exists(),
        ]);
    }
}
