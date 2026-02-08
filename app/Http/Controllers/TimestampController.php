<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TimestampTypeEnum;
use App\Http\Requests\DestroyTimestampRequest;
use App\Http\Requests\FillTimestampRequest;
use App\Http\Requests\MergeTimestampRequest;
use App\Http\Requests\StoreTimestampRequest;
use App\Http\Requests\UpdateTimestampPaidRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TimestampResource;
use App\Jobs\CalculateWeekBalance;
use App\Jobs\MenubarRefresh;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\TimestampService;
use App\Settings\ProjectSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class TimestampController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Carbon $datetime, ?string $endDatetime = null, ?string $type = null)
    {
        if ($endDatetime) {
            $endDatetime = Date::parse($endDatetime);
        }

        $timestampBefore = Timestamp::where('ended_at', '<=', $datetime)
            ->where('ended_at', '>=', $datetime->copy()->startOfDay())
            ->orderByDesc('started_at')
            ->first();

        $timestampAfter = Timestamp::where('started_at', '>=', $datetime)
            ->where(function ($query) use ($datetime): void {
                $query->where('ended_at', '<=', $datetime->copy()->endOfDay())
                    ->orWhere(function ($query) use ($datetime): void {
                        $query->whereNull('ended_at')->where('started_at', '<=', $datetime->copy()->endOfDay());
                    });
            })
            ->orderBy('started_at')
            ->first();

        $minTime = $timestampBefore && $type !== 'work' ? $timestampBefore->ended_at : $datetime->copy()->startOfDay();
        $maxTime = $timestampAfter && $type !== 'work' ? $timestampAfter->started_at : $datetime->copy()->endOfDay();

        if ($maxTime > now()) {
            $maxTime = now();
        }

        if ($endDatetime) {
            if ($minTime->copy()->addMinutes(10)->addSecond() > $datetime && $datetime < now()) {
                $datetime = $minTime;
            }
            if ($maxTime->copy()->subMinutes(10)->subSecond() < $endDatetime && $endDatetime < now()) {
                $endDatetime = $maxTime;
            }
        }

        if ($datetime < $minTime) {
            $datetime = $minTime;
        }

        if ($datetime > $maxTime) {
            $datetime = $maxTime;
        }

        if ($endDatetime && $endDatetime < $minTime) {
            $endDatetime = $minTime;
        }

        if ($endDatetime && $endDatetime > $maxTime) {
            $endDatetime = $maxTime;
        }

        Inertia::share(['date' => $datetime->format('Y-m-d')]);

        return Inertia::modal('Timestamp/Create', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime->format('H:i'),
            'start_time' => $endDatetime ? $datetime->format('H:i') : null,
            'end_time' => $endDatetime ? $endDatetime->format('H:i') : null,
            'submit_route' => route('timestamp.store', ['datetime' => $datetime->format('Y-m-d H:i:s')]),
            'projects' => ProjectResource::collection(Project::scopes('sortedByLatestTimestamp')->get()),
            'type' => $type === 'work' ? 'break' : null,
        ])->baseRoute('overview.day.show', ['date' => now()->format('Y-m-d')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimestampRequest $request, Carbon $datetime)
    {
        $data = $request->validated();

        $startTime = $datetime->setTimezone(config('app.timezone'))->copy()->setTimeFromTimeString($data['started_at']);
        $endTime = $datetime->setTimezone(config('app.timezone'))->copy()->setTimeFromTimeString($data['ended_at']);

        if ($startTime > $endTime) {
            return back()->withErrors([
                'started_at' => __('app.the start time must be before the end time.'),
            ]);
        }

        if ($startTime->isAfter(now())) {
            return back()->withErrors([
                'started_at' => __('app.the start time must be in the past.'),
            ]);
        }

        if ($endTime->isAfter(now())) {
            return back()->withErrors([
                'ended_at' => __('app.the end time must be in the past.'),
            ]);
        }

        $lastTimestamp = Timestamp::where('ended_at', '<=', $startTime->copy()->endOfMinute())
            ->where('ended_at', '>=', $startTime->copy()->startOfDay())
            ->orderByDesc('started_at')
            ->first();
        $nextTimestamp = Timestamp::where('started_at', '>=', $endTime)
            ->where(function ($query) use ($endTime, $datetime): void {
                $query->where('ended_at', '<=', $endTime->copy()->endOfDay())
                    ->orWhere(function ($query) use ($datetime): void {
                        $query->whereNull('ended_at')->where('started_at', '<=', $datetime->copy()->endOfDay());
                    });
            })
            ->orderBy('started_at')
            ->first();

        if ($lastTimestamp && $lastTimestamp->ended_at->format('Y-m-d H:i') === $startTime->format('Y-m-d H:i')) {
            $startTime = $lastTimestamp->ended_at;
        }

        if (($lastTimestamp && $lastTimestamp->ended_at > $startTime) || ($nextTimestamp && $nextTimestamp->started_at < $endTime)) {
            return back()->withErrors([
                'started_at' => __('app.the start or end time overlaps with another time span.'),
            ]);
        }

        if ($nextTimestamp && $nextTimestamp->started_at->format('Y-m-d H:i') === $endTime->format('Y-m-d H:i')) {
            $endTime = $nextTimestamp->started_at;
        }
        if ($endTime->format('H:i') === '23:59') {
            $endTime = $endTime->endOfDay();
        }

        Inertia::share(['date' => $datetime->format('Y-m-d')]);

        TimestampService::create($startTime, $endTime, TimestampTypeEnum::tryFrom($data['type']), $data['description'] ?? null, $data['project_id'] ?? null);

        dispatch(new CalculateWeekBalance);

        return to_route('overview.day.show', ['date' => $datetime->format('Y-m-d')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timestamp $timestamp)
    {
        $timestamp->load('project');

        $timestampBefore = Timestamp::where('ended_at', '<=', $timestamp->started_at)
            ->where('ended_at', '>=', $timestamp->started_at->copy()->startOfDay())
            ->orderByDesc('started_at')
            ->first();
        $minTime = $timestampBefore ? $timestampBefore->ended_at : $timestamp->started_at->copy()->startOfDay();

        $maxTime = now();
        if ($timestamp->ended_at) {
            $timestampAfter = Timestamp::where('started_at', '>=', $timestamp->ended_at)
                ->where(function ($query) use ($timestamp): void {
                    $query->where('ended_at', '<=', $timestamp->ended_at->copy()->endOfDay())
                        ->orWhere(function ($query) use ($timestamp): void {
                            $query->whereNull('ended_at')->where('started_at', '<=', $timestamp->started_at->copy()->endOfDay());
                        });
                })
                ->orderBy('started_at')
                ->first();
            $maxTime = $timestampAfter ? $timestampAfter->started_at : $timestamp->ended_at->copy()->endOfDay();
        }

        Inertia::share(['date' => $timestamp->created_at->format('Y-m-d')]);

        return Inertia::modal('Timestamp/Edit', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime?->format('H:i'),
            'submit_route' => route('timestamp.update', ['timestamp' => $timestamp->id]),
            'timestamp' => TimestampResource::make($timestamp),
            'projects' => ProjectResource::collection(Project::scopes('sortedByLatestTimestamp')->get()),
        ])->baseRoute('overview.day.show', ['date' => $timestamp->created_at->format('Y-m-d')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTimestampRequest $request, Timestamp $timestamp)
    {
        $data = $request->validated();

        $startTime = $timestamp->started_at->copy()->setTimeFromTimeString($data['started_at']);

        $hasEndedAt = $request->has('ended_at');
        $workingEndTime = $hasEndedAt ? $timestamp->ended_at->copy()->setTimeFromTimeString($data['ended_at']) : now();

        if ($startTime > $workingEndTime) {
            return back()->withErrors([
                'started_at' => __('app.the start time must be before the end time.'),
            ]);
        }

        if ($startTime->isAfter(now())) {
            return back()->withErrors([
                'started_at' => __('app.the start time must be in the past.'),
            ]);
        }

        if ($hasEndedAt && $workingEndTime->isAfter(now())) {
            return back()->withErrors([
                'ended_at' => __('app.the end time must be in the past.'),
            ]);
        }

        if ($startTime->format('Y-m-d H:i') === $timestamp->started_at->format('Y-m-d H:i')) {
            $startTime = $timestamp->started_at;
        }

        if ($hasEndedAt && $workingEndTime->format('Y-m-d H:i') === $timestamp->ended_at->format('Y-m-d H:i')) {
            $workingEndTime = $timestamp->ended_at;
        }

        $lastTimestamp = Timestamp::where('ended_at', '<=', $startTime)
            ->where('ended_at', '>=', $startTime->copy()->startOfDay())
            ->orderByDesc('started_at')
            ->first();

        $nextTimestamp = Timestamp::where('started_at', '>=', $workingEndTime)
            ->where('ended_at', '<=', $workingEndTime->copy()->endOfDay())
            ->orderBy('started_at')
            ->first();

        if (($lastTimestamp && $lastTimestamp->ended_at > $startTime) || ($nextTimestamp && $nextTimestamp->started_at < $workingEndTime)) {
            return back()->withErrors([
                'started_at' => __('app.the start or end time overlaps with another time span.'),
            ]);
        }

        if ($lastTimestamp && $lastTimestamp->ended_at->format('Y-m-d H:i') === $startTime->format('Y-m-d H:i')) {
            $startTime = $lastTimestamp->ended_at;
        }
        if ($hasEndedAt && $nextTimestamp && $nextTimestamp->started_at->format('Y-m-d H:i') === $workingEndTime->format('Y-m-d H:i')) {
            $workingEndTime = $nextTimestamp->started_at;
        }
        if ($hasEndedAt && $workingEndTime->format('H:i') === '23:59') {
            $workingEndTime = $workingEndTime->endOfDay();
        }

        Inertia::share(['date' => $startTime->format('Y-m-d')]);

        $timestamp->type = $data['type'];
        $timestamp->started_at = $startTime;
        if ($hasEndedAt) {
            $timestamp->ended_at = $workingEndTime;
            $timestamp->last_ping_at = $workingEndTime;
        }
        $timestamp->description = $data['description'] ?? null;
        $timestamp->project_id = $data['project_id'] ?? null;
        $timestamp->save();

        if (! $timestamp->ended_at && $timestamp->started_at->isToday() && $timestamp->project_id) {
            $projectSettings = resolve(ProjectSettings::class);
            $projectSettings->currentProject = $timestamp->project_id;
            $projectSettings->save();
        }

        dispatch(new CalculateWeekBalance);
        if (! $hasEndedAt || $startTime->isToday()) {
            dispatch_sync(new MenubarRefresh);
        }

        return to_route('overview.day.show', ['date' => $startTime->format('Y-m-d')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTimestampRequest $request, Timestamp $timestamp): Redirector|RedirectResponse
    {
        Inertia::share(['date' => $timestamp->started_at->format('Y-m-d')]);
        $date = $timestamp->started_at->format('Y-m-d');
        $isToday = $timestamp->started_at->isToday();
        $request->validated();
        $timestamp->delete();

        dispatch(new CalculateWeekBalance);

        if ($isToday) {
            dispatch_sync(new MenubarRefresh);
        }

        return to_route('overview.day.show', ['date' => $date]);
    }

    public function fill(FillTimestampRequest $request): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $timestampBefore = Timestamp::find($data['timestamp_before']);
        $timestampAfter = Timestamp::find($data['timestamp_after']);

        Timestamp::create([
            'type' => $data['fill_with'],
            'started_at' => $timestampBefore->ended_at,
            'ended_at' => $timestampAfter->started_at,
            'last_ping_at' => $timestampAfter->started_at,
        ]);

        dispatch(new CalculateWeekBalance);

        if ($timestampBefore->ended_at->isToday()) {
            dispatch_sync(new MenubarRefresh);
        }

        return to_route('overview.day.show', ['date' => $timestampBefore->created_at->format('Y-m-d')]);
    }

    public function merge(MergeTimestampRequest $request): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $timestamp = Timestamp::findOrFail($data['timestamp']);
        $timestampBefore = Timestamp::findOrFail($data['timestamp_before']);

        $mergedTimestamp = TimestampService::merge($timestamp, $timestampBefore);

        if (! $mergedTimestamp instanceof Timestamp) {
            return back();
        }

        $date = $mergedTimestamp->started_at->format('Y-m-d');

        Inertia::share(['date' => $date]);

        dispatch(new CalculateWeekBalance);

        if (! $mergedTimestamp->ended_at || $mergedTimestamp->started_at->isToday()) {
            dispatch_sync(new MenubarRefresh);
        }

        return to_route('overview.day.show', ['date' => $date]);
    }

    public function updatePaid(UpdateTimestampPaidRequest $request, Timestamp $timestamp): void
    {
        $data = $request->validated();

        $timestamp->paid = $data['paid'];
        $timestamp->save();
    }
}
