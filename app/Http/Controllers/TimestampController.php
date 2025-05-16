<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTimestampRequest;
use App\Http\Requests\FillTimestampRequest;
use App\Http\Requests\StoreTimestampRequest;
use App\Http\Resources\TimestampResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\Timestamp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class TimestampController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Carbon $datetime, ?string $endDatetime = null)
    {
        if ($endDatetime) {
            $endDatetime = Carbon::parse($endDatetime);
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

        $minTime = $timestampBefore ? $timestampBefore->ended_at : $datetime->copy()->startOfDay();
        $maxTime = $timestampAfter ? $timestampAfter->started_at : $datetime->copy()->endOfDay();

        if ($maxTime > now()) {
            $maxTime = now();
        }

        if ($endDatetime) {
            if ($minTime->copy()->addMinutes(10) > $datetime && $datetime < now()) {
                $datetime = $minTime;
            }
            if ($maxTime->copy()->subMinutes(10) < $endDatetime && $endDatetime < now()) {
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

        Inertia::share(['date' => $datetime->format('d.m.Y')]);

        return Inertia::modal('Timestamp/Create', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime->format('H:i'),
            'start_time' => $endDatetime ? $datetime->format('H:i') : null,
            'end_time' => $endDatetime ? $endDatetime->format('H:i') : null,
            'submit_route' => route('timestamp.store', ['datetime' => $datetime->format('Y-m-d H:i:s')]),
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
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start time must be before the end time.'),
            ]);
        }

        if ($startTime->isAfter(now())) {
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start time must be in the past.'),
            ]);
        }

        if ($endTime->isAfter(now())) {
            return redirect()->back()->withErrors([
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
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start or end time overlaps with another time span.'),
            ]);
        }

        if ($nextTimestamp && $nextTimestamp->started_at->format('Y-m-d H:i') === $endTime->format('Y-m-d H:i')) {
            $endTime = $nextTimestamp->started_at;
        }
        if ($endTime->format('H:i') === '23:59') {
            $endTime = $endTime->endOfDay();
        }

        Inertia::share(['date' => $datetime->format('d.m.Y')]);

        Timestamp::create([
            'type' => $data['type'],
            'started_at' => $startTime,
            'ended_at' => $endTime,
            'last_ping_at' => $endTime,
            'description' => $data['description'] ?? null,
        ]);

        CalculateWeekBalance::dispatch();

        return redirect()->route('overview.day.show', ['date' => $datetime->format('Y-m-d')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timestamp $timestamp)
    {
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

        Inertia::share(['date' => $timestamp->created_at->format('d.m.Y')]);

        return Inertia::modal('Timestamp/Edit', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime?->format('H:i'),
            'submit_route' => route('timestamp.update', ['timestamp' => $timestamp->id]),
            'timestamp' => TimestampResource::make($timestamp),
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
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start time must be before the end time.'),
            ]);
        }

        if ($startTime->isAfter(now())) {
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start time must be in the past.'),
            ]);
        }

        if ($hasEndedAt && $workingEndTime->isAfter(now())) {
            return redirect()->back()->withErrors([
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
            return redirect()->back()->withErrors([
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

        Inertia::share(['date' => $startTime->format('d.m.Y')]);

        $timestamp->type = $data['type'];
        $timestamp->started_at = $startTime;
        if ($hasEndedAt) {
            $timestamp->ended_at = $workingEndTime;
            $timestamp->last_ping_at = $workingEndTime;
        }
        $timestamp->description = $data['description'] ?? null;
        $timestamp->save();

        CalculateWeekBalance::dispatch();
        if (! $hasEndedAt || $startTime->isToday()) {
            Artisan::call('menubar:refresh');
        }

        return redirect()->route('overview.day.show', ['date' => $startTime->format('Y-m-d')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTimestampRequest $request, Timestamp $timestamp)
    {
        Inertia::share(['date' => $timestamp->started_at->format('d.m.Y')]);
        $date = $timestamp->started_at->format('Y-m-d');
        $isToday = $timestamp->started_at->isToday();
        $request->validated();
        $timestamp->delete();

        CalculateWeekBalance::dispatch();

        if ($isToday) {
            Artisan::call('menubar:refresh');
        }

        return redirect()->route('overview.day.show', ['date' => $date]);
    }

    public function fill(FillTimestampRequest $request)
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

        CalculateWeekBalance::dispatch();

        if ($timestampBefore->ended_at->isToday()) {
            Artisan::call('menubar:refresh');
        }

        return redirect()->route('overview.day.show', ['date' => $timestampBefore->created_at->format('Y-m-d')]);
    }
}
