<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TimestampResource;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DayController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $date)
    {
        $date = Carbon::parse($date);
        $startDay = $date->copy()->startOfDay();
        $endDay = $date->copy()->endOfDay();

        return Inertia::render('Day/Edit', [
            'timestamps' => TimestampResource::collection(TimestampService::getTimestamps($startDay, $endDay, true)),
            'dayWorkTime' => TimestampService::getWorkTime($startDay, $endDay),
            'dayBreakTime' => TimestampService::getBreakTime($startDay, $endDay),
            'dayPlan' => TimestampService::getPlan(strtolower($date->englishDayOfWeek)),
            'dayFallbackPlan' => TimestampService::getFallbackPlan($startDay, $endDay),
            'dayNoWorkTime' => TimestampService::getNoWorkTime($startDay),
            'absences' => TimestampService::getAbsence($startDay),
            'date' => $date->format('d.m.Y'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}
