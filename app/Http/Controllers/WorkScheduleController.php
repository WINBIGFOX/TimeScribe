<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTimestampRequest;
use App\Http\Requests\DestroyWorkScheduleRequest;
use App\Http\Requests\StoreWorkScheduleRequest;
use App\Http\Resources\WorkScheduleResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class WorkScheduleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::modal('WorkSchedule/Create', [
            'submit_route' => route('work-schedule.store'),
        ])->baseRoute('settings.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkScheduleRequest $request)
    {
        $data = $request->validated();

        WorkSchedule::create($data);

        Cache::flush();
        CalculateWeekBalance::dispatch();

        return redirect()->route('settings.edit');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkSchedule $workSchedule)
    {
        return Inertia::modal('WorkSchedule/Edit', [
            'workSchedule' => WorkScheduleResource::make($workSchedule),
            'submit_route' => route('work-schedule.update', ['work_schedule' => $workSchedule->id]),
            'destroy_route' => route('work-schedule.destroy', ['work_schedule' => $workSchedule->id]),
        ])->baseRoute('settings.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreWorkScheduleRequest $request, WorkSchedule $workSchedule)
    {
        $data = $request->validated();

        $workSchedule->update($data);

        Cache::flush();
        CalculateWeekBalance::dispatch();

        return redirect()->route('settings.edit');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyWorkScheduleRequest $request, WorkSchedule $workSchedule)
    {
        $request->validated();
        $workSchedule->delete();

        Cache::flush();
        CalculateWeekBalance::dispatch();

        return redirect()->route('settings.edit');
    }
}
