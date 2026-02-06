<?php

declare(strict_types=1);

namespace App\Http\Controllers\OvertimeAdjustment;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyOvertimeAdjustmentRequest;
use App\Http\Requests\StoreOvertimeAdjustmentRequest;
use App\Http\Resources\AbsenceResource;
use App\Http\Resources\OvertimeAdjustmentResource;
use App\Http\Resources\WeekBalanceResource;
use App\Jobs\CalculateWeekBalance;
use App\Models\OvertimeAdjustment;
use App\Models\WeekBalance;
use App\Services\HolidayService;
use App\Services\TimestampService;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class OvertimeAdjustmentController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Carbon $date): Response
    {
        Inertia::share(['date' => $date->format('Y-m-d')]);

        $weekBalances = WeekBalance::where('start_week_at', '<=', now())->latest('start_week_at')->get();

        $date = $date->isFuture() ? now() : $date;

        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $weekDays = collect(new DatePeriod($startOfWeek, new DateInterval('P1D'), $endOfWeek))->map(function (DateTime $date): array {
            $date = Date::parse($date);

            return [
                'plan' => TimestampService::getPlan($date),
                'fallbackPlan' => TimestampService::getFallbackPlan($date),
                'date' => DateHelper::toResourceArray($date),
                'workTime' => TimestampService::getWorkTime($date),
                'activeWork' => TimestampService::getActiveWork($date),
                'absences' => AbsenceResource::collection(TimestampService::getAbsence($date)),
                'isHoliday' => HolidayService::isHoliday($date),
            ];
        });

        $overtimeAdjustments = OvertimeAdjustment::whereBetween('effective_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])->get();

        return Inertia::modal('OvertimeAdjustment/Show', [
            'date' => $date->format('Y-m-d'),
            'weekBalances' => WeekBalanceResource::collection($weekBalances),
            'weekdays' => $weekDays,
            'balance' => TimestampService::getBalance($date),
            'week' => $date->week,
            'year' => $date->weekYear,
            'overtimeAdjustments' => OvertimeAdjustmentResource::collection($overtimeAdjustments),
            'allOvertimeAdjustments' => OvertimeAdjustmentResource::collection(OvertimeAdjustment::all()),
        ])->baseRoute('overview.week.show', ['date' => $date->format('Y-m-d')]);
    }

    public function store(StoreOvertimeAdjustmentRequest $request, Carbon $date): RedirectResponse
    {
        $data = $request->validated();

        OvertimeAdjustment::create([
            'effective_date' => $data['effective_date'],
            'seconds' => $data['seconds'],
            'type' => $data['type'],
            'note' => $data['note'] ?? null,
        ]);

        dispatch_sync(new CalculateWeekBalance);

        return to_route('overtime-adjustment.show', ['date' => $date->format('Y-m-d')]);
    }

    public function update(StoreOvertimeAdjustmentRequest $request, Carbon $date, OvertimeAdjustment $overtimeAdjustment): RedirectResponse
    {
        $data = $request->validated();

        $overtimeAdjustment->update([
            'effective_date' => $data['effective_date'],
            'seconds' => $data['seconds'],
            'type' => $data['type'],
            'note' => $data['note'] ?? null,
        ]);

        dispatch_sync(new CalculateWeekBalance);

        return to_route('overtime-adjustment.show', ['date' => $date->format('Y-m-d')]);
    }

    public function destroy(DestroyOvertimeAdjustmentRequest $request, Carbon $date, OvertimeAdjustment $overtimeAdjustment): RedirectResponse
    {
        $request->validated();

        $overtimeAdjustment->delete();

        dispatch_sync(new CalculateWeekBalance);

        return to_route('overtime-adjustment.show', ['date' => $date->format('Y-m-d')]);
    }
}
