<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTimestampRequest;
use App\Http\Requests\FillTimestampRequest;
use App\Http\Requests\StoreTimestampRequest;
use App\Http\Requests\UpdateTimestampRequest;
use App\Http\Resources\TimestampResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\Timestamp;
use Inertia\Inertia;

class TimestampController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreTimestampRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Timestamp $timestamp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timestamp $timestamp)
    {
        $timestamp->append(['can_start_edit', 'can_end_edit']);

        return Inertia::modal('Timestamp/Edit', [
            'submit_route' => route('timestamp.update', ['timestamp' => $timestamp->id]),
            'timestamp' => TimestampResource::make($timestamp),
        ])->baseRoute('day.edit', ['date' => $timestamp->created_at->format('Y-m-d')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimestampRequest $request, Timestamp $timestamp)
    {
        return redirect()->route('day.edit', ['date' => $timestamp->created_at->format('Y-m-d')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTimestampRequest $request, Timestamp $timestamp)
    {
        $date = $timestamp->started_at->format('Y-m-d');
        $request->validated();
        $timestamp->delete();

        CalculateWeekBalance::dispatch();

        return redirect()->route('day.edit', ['date' => $date]);
    }

    public function fill(FillTimestampRequest $request)
    {
        $data = $request->validated();

        $firstTimestamp = Timestamp::find($data['first_timestamp']);
        $secondTimestamp = Timestamp::find($data['second_timestamp']);

        Timestamp::create([
            'type' => $data['fill_with'],
            'started_at' => $firstTimestamp->ended_at,
            'ended_at' => $secondTimestamp->started_at,
            'last_ping_at' => $secondTimestamp->started_at,
        ]);

        CalculateWeekBalance::dispatch();

        return redirect()->route('day.edit', ['date' => $firstTimestamp->created_at->format('Y-m-d')]);
    }
}
