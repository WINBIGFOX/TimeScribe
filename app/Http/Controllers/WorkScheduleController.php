<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyWorkScheduleRequest;
use App\Http\Requests\StoreWorkScheduleRequest;
use App\Http\Resources\WorkScheduleResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\WorkSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class WorkScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('WorkSchedule/Index', [
            'workSchedules' => WorkScheduleResource::collection(WorkSchedule::orderByDesc('valid_from')->get()->append(['is_current'])),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::modal('WorkSchedule/Create', [
            'submit_route' => route('work-schedule.store'),
        ])->baseRoute('work-schedule.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkScheduleRequest $request): Redirector|RedirectResponse
    {
        $data = $request->validated();

        WorkSchedule::create($data);

        Cache::flush();
        dispatch(new CalculateWeekBalance);

        return to_route('work-schedule.index');
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
        ])->baseRoute('work-schedule.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreWorkScheduleRequest $request, WorkSchedule $workSchedule): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $workSchedule->update($data);

        Cache::flush();
        dispatch(new CalculateWeekBalance);

        return to_route('work-schedule.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyWorkScheduleRequest $request, WorkSchedule $workSchedule): Redirector|RedirectResponse
    {
        $request->validated();
        $workSchedule->delete();

        Cache::flush();
        dispatch(new CalculateWeekBalance);

        return to_route('work-schedule.index');
    }
}
