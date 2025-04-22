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
use Inertia\Inertia;

class TimestampController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Carbon $datetime)
    {
        $timestampBefore = Timestamp::where('ended_at', '<=', $datetime)
            ->where('ended_at', '>=', $datetime->copy()->startOfDay())
            ->latest()
            ->first();

        $timestampAfter = Timestamp::where('started_at', '>=', $datetime)
            ->where('ended_at', '<=', $datetime->copy()->endOfDay())
            ->oldest()
            ->first();

        $minTime = $timestampBefore ? $timestampBefore->ended_at : $datetime->copy()->startOfDay();
        $maxTime = $timestampAfter ? $timestampAfter->started_at : $datetime->copy()->endOfDay();

        // dd($timestampBefore);

        Inertia::share(['date' => $datetime->format('d.m.Y')]);

        return Inertia::modal('Timestamp/Create', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime->format('H:i'),
            'submit_route' => route('timestamp.store', ['datetime' => $datetime->format('Y-m-d H:i:s')]),
        ])->baseRoute('overview.day.show', ['date' => now()->format('Y-m-d')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimestampRequest $request, Carbon $datetime)
    {
        $data = $request->validated();

        $startTime = $datetime->copy()->setTimeFromTimeString($data['started_at']);
        $endTime = $datetime->copy()->setTimeFromTimeString($data['ended_at']);

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

        $lastTimestamp = Timestamp::where('ended_at', '<=', $startTime)
            ->where('ended_at', '>=', $startTime->copy()->startOfDay())
            ->latest()
            ->first();
        $nextTimestamp = Timestamp::where('started_at', '>=', $endTime)
            ->where('ended_at', '<=', $endTime->copy()->endOfDay())
            ->oldest()
            ->first();

        if (($lastTimestamp && $lastTimestamp->ended_at > $startTime) || ($nextTimestamp && $nextTimestamp->started_at < $endTime)) {
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start or end time overlaps with another time span.'),
            ]);
        }

        if ($lastTimestamp && $lastTimestamp->ended_at->format('Y-m-d H:i') === $startTime->format('Y-m-d H:i')) {
            $startTime = $lastTimestamp->ended_at;
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
            ->latest()
            ->first();

        $timestampAfter = Timestamp::where('started_at', '>=', $timestamp->ended_at)
            ->where('ended_at', '<=', $timestamp->ended_at->copy()->endOfDay())
            ->oldest()
            ->first();

        $minTime = $timestampBefore ? $timestampBefore->ended_at : $timestamp->started_at->copy()->startOfDay();
        $maxTime = $timestampAfter ? $timestampAfter->started_at : $timestamp->ended_at->copy()->endOfDay();

        Inertia::share(['date' => $timestamp->created_at->format('d.m.Y')]);

        return Inertia::modal('Timestamp/Edit', [
            'min_time' => $minTime->format('H:i'),
            'max_time' => $maxTime->format('H:i'),
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
        $endTime = $timestamp->ended_at->copy()->setTimeFromTimeString($data['ended_at']);

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

        if ($startTime->format('Y-m-d H:i') === $timestamp->started_at->format('Y-m-d H:i')) {
            $startTime = $timestamp->started_at;
        }

        if ($endTime->format('Y-m-d H:i') === $timestamp->ended_at->format('Y-m-d H:i')) {
            $endTime = $timestamp->ended_at;
        }

        $lastTimestamp = Timestamp::where('ended_at', '<=', $startTime)
            ->where('ended_at', '>=', $startTime->copy()->startOfDay())
            ->latest()
            ->first();
        $nextTimestamp = Timestamp::where('started_at', '>=', $endTime)
            ->where('ended_at', '<=', $endTime->copy()->endOfDay())
            ->oldest()
            ->first();

        if (($lastTimestamp && $lastTimestamp->ended_at > $startTime) || ($nextTimestamp && $nextTimestamp->started_at < $endTime)) {
            return redirect()->back()->withErrors([
                'started_at' => __('app.the start or end time overlaps with another time span.'),
            ]);
        }

        if ($lastTimestamp && $lastTimestamp->ended_at->format('Y-m-d H:i') === $startTime->format('Y-m-d H:i')) {
            $startTime = $lastTimestamp->ended_at;
        }
        if ($nextTimestamp && $nextTimestamp->started_at->format('Y-m-d H:i') === $endTime->format('Y-m-d H:i')) {
            $endTime = $nextTimestamp->started_at;
        }
        if ($endTime->format('H:i') === '23:59') {
            $endTime = $endTime->endOfDay();
        }

        Inertia::share(['date' => $startTime->format('d.m.Y')]);

        $timestamp->update([
            'type' => $data['type'],
            'started_at' => $startTime,
            'ended_at' => $endTime,
            'last_ping_at' => $endTime,
            'description' => $data['description'] ?? null,
        ]);

        CalculateWeekBalance::dispatch();

        return redirect()->route('overview.day.show', ['date' => $startTime->format('Y-m-d')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTimestampRequest $request, Timestamp $timestamp)
    {
        Inertia::share(['date' => $timestamp->started_at->format('d.m.Y')]);
        $date = $timestamp->started_at->format('Y-m-d');
        $request->validated();
        $timestamp->delete();

        CalculateWeekBalance::dispatch();

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

        return redirect()->route('overview.day.show', ['date' => $timestampBefore->created_at->format('Y-m-d')]);
    }
}
