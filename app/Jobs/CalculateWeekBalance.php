<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Timestamp;
use App\Models\WeekBalance;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Native\Laravel\Facades\Settings;

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
        Carbon::setLocale(Settings::get('locale'));
        $firstTimestamp = Timestamp::orderBy('created_at')->first();

        $startWeek = $firstTimestamp->created_at->clone()->startOfWeek();
        $endWeek = $firstTimestamp->created_at->clone()->endOfWeek();
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
    }
}
