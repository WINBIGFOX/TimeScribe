<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AbsenceTypeEnum;
use App\Helpers\DateHelper;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Models\Absence;
use App\Services\TimestampService;
use Carbon\Carbon;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('absence.show', ['date' => Carbon::now()->format('Y-m-d')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsenceRequest $request, string $date)
    {
        $data = $request->validated();

        if ($data['type'] === AbsenceTypeEnum::SICK) {
            $data['duration'] = null;
        }

        Absence::create($data);

        return redirect()->route('absence.show', ['date' => $date]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $date)
    {
        $date = Carbon::parse($date);
        $startDate = $date->clone()->subMonth();
        $endDate = $date->clone()->addMonth();

        return Inertia::render('Absence/Show', [
            'absences' => AbsenceResource::collection(
                Absence::whereBetween('date', [$startDate, $endDate])->get()
            ),
            'holidays' => TimestampService::getHoliday([$startDate->year, $endDate->year])->map(function ($holidayDate) {
                return DateHelper::toResourceArray($holidayDate);
            }),
            'workdaysPlan' => Settings::get('workdays', []),
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $date, Absence $absence)
    {
        $absence->delete();

        return redirect()->route('absence.show', ['date' => $date]);
    }
}
