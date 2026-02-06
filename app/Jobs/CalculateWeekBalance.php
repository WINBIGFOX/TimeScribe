<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\OvertimeAdjustmentTypeEnum;
use App\Models\OvertimeAdjustment;
use App\Models\Timestamp;
use App\Models\WeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

class CalculateWeekBalance implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            new LocaleService;
            $settings = resolve(GeneralSettings::class);
            Date::setLocale(str_replace('-', '_', $settings->locale ?? config('app.fallback_locale')));
            $firstTimestamp = Timestamp::orderBy('started_at')->first();

            if (! $firstTimestamp) {
                WeekBalance::truncate();

                return;
            }

            $startWeek = $firstTimestamp->started_at->clone()->startOfWeek();
            $endWeek = $firstTimestamp->started_at->clone()->endOfWeek();
            $lastCalculatedWeek = now()->addWeeks(1)->startOfWeek();

            $overtimeAdjustments = OvertimeAdjustment::oldest('effective_date')->get();
            $weekEndBalance = 0;

            WeekBalance::truncate();

            while ($lastCalculatedWeek->greaterThanOrEqualTo($startWeek)) {

                $weekStartBalance = $weekEndBalance;

                $adjustments = $overtimeAdjustments->whereBetween('effective_date', [$startWeek, $endWeek]);
                $relativeAdjustments = $adjustments->where('type', OvertimeAdjustmentTypeEnum::Relative);
                $absoluteAdjustments = $adjustments->where('type', OvertimeAdjustmentTypeEnum::Absolute);

                $adjustmentBalance = 0;

                if ($absoluteAdjustments->isEmpty() && $relativeAdjustments->isNotEmpty()) {
                    $adjustmentBalance = $relativeAdjustments->sum('seconds');
                }

                if ($absoluteAdjustments->isEmpty()) {
                    $workTime = TimestampService::getWorkTime($startWeek, $endWeek);
                    $weekPlan = TimestampService::getWeekPlan($startWeek);
                    $balance = $workTime - ($weekPlan * 3600);

                } else {
                    $periode = CarbonPeriod::create($startWeek, $endWeek);
                    $balance = 0;

                    foreach ($periode as $rangeDate) {
                        $workTime = TimestampService::getWorkTime($rangeDate);
                        $plan = TimestampService::getPlan($rangeDate);

                        $absoluteDayAdjustments = $absoluteAdjustments->where('effective_date', $rangeDate);
                        $relativeDayAdjustments = $relativeAdjustments->where('effective_date', $rangeDate);

                        if ($absoluteDayAdjustments->isNotEmpty()) {
                            $balance = 0;
                            $adjustmentBalance = 0;
                            $weekEndBalance = $absoluteDayAdjustments->last()->seconds;
                        }
                        if ($relativeDayAdjustments->isNotEmpty()) {
                            $adjustmentBalance += $relativeDayAdjustments->sum('seconds');
                        }
                        $balance += ($workTime - ($plan * 3600));
                    }

                }
                $weekEndBalance += $balance + $adjustmentBalance;

                WeekBalance::updateOrCreate(
                    ['start_week_at' => $startWeek, 'end_week_at' => $endWeek],
                    [
                        'balance' => $balance,
                        'start_balance' => $weekStartBalance,
                        'end_balance' => $weekEndBalance,
                    ]
                );

                $startWeek->addWeek();
                $endWeek->addWeek();
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to calculate week balance: '.$e->getMessage());

            return;
        }
    }
}
