<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTimestampRequest;
use App\Http\Requests\StoreTimestampRequest;
use App\Http\Requests\UpdateTimestampRequest;
use App\Models\Timestamp;

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimestampRequest $request, Timestamp $timestamp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTimestampRequest $request, Timestamp $timestamp)
    {
        $date = $timestamp->created_at->format('Y-m-d');
        $request->validated();
        $timestamp->delete();

        return redirect()->route('day.edit', ['date' => $date]);
    }
}
