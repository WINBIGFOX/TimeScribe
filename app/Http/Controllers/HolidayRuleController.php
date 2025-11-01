<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyHolidayRuleRequest;
use App\Http\Requests\StoreHolidayRuleRequest;
use App\Services\HolidayService;
use Illuminate\Support\Facades\Date;

class HolidayRuleController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHolidayRuleRequest $request): void
    {
        $date = $request->validated()['date'];
        HolidayService::addHoliday(Date::create($date));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyHolidayRuleRequest $request): void
    {
        $date = $request->validated()['date'];
        HolidayService::removeHoliday(Date::create($date));
    }
}
