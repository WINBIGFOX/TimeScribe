<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Timestamp;
use App\Models\WeekBalance;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateWeekBalance implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            new LocaleService;
            $settings = app(GeneralSettings::class);
            Carbon::setLocale(str_replace('-', '_', $settings->locale ?? config('app.fallback_locale')));
            $firstTimestamp = Timestamp::orderBy('started_at')->first();

            if (! $firstTimestamp) {
                return;
            }

            $startWeek = $firstTimestamp->started_at->clone()->startOfWeek();
            $endWeek = $firstTimestamp->started_at->clone()->endOfWeek();
            $lastCalculatedWeek = now()->addWeeks(1)->startOfWeek();

            WeekBalance::truncate();

            while ($startWeek->isBefore($lastCalculatedWeek)) {

                $workTime = TimestampService::getWorkTime($startWeek, $endWeek);
                $weekPlan = TimestampService::getWeekPlan($startWeek);
                $balance = $workTime - ($weekPlan * 3600);

                WeekBalance::updateOrCreate(
                    ['start_week_at' => $startWeek, 'end_week_at' => $endWeek],
                    ['balance' => $balance]
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
