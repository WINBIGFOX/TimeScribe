<?php

declare(strict_types=1);

use App\Enums\OvertimeAdjustmentTypeEnum;
use App\Enums\TimestampTypeEnum;
use App\Jobs\CalculateWeekBalance;
use App\Models\OvertimeAdjustment;
use App\Models\Timestamp;
use App\Models\WeekBalance;
use App\Models\WorkSchedule;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Date;

it('calculates weekly balances with relative adjustments', function (): void {
    Date::setTestNow(Date::parse('2025-01-08 12:00:00'));

    $settings = resolve(GeneralSettings::class);
    $settings->timezone = 'UTC';
    $settings->save();

    WorkSchedule::create([
        'monday' => 8,
        'tuesday' => 8,
        'wednesday' => 8,
        'thursday' => 8,
        'friday' => 8,
        'saturday' => 0,
        'sunday' => 0,
        'valid_from' => Date::parse('2024-12-30'),
    ]);

    foreach ([
        '2025-01-06',
        '2025-01-07',
        '2025-01-08',
        '2025-01-09',
        '2025-01-10',
    ] as $workDay) {
        Timestamp::create([
            'type' => TimestampTypeEnum::WORK,
            'started_at' => Date::parse($workDay.' 09:00:00'),
            'ended_at' => Date::parse($workDay.' 17:00:00'),
            'last_ping_at' => Date::parse($workDay.' 17:00:00'),
        ]);
    }

    OvertimeAdjustment::create([
        'effective_date' => Date::parse('2025-01-08'),
        'type' => OvertimeAdjustmentTypeEnum::Relative,
        'seconds' => 3600,
        'note' => 'carryover',
    ]);

    (new CalculateWeekBalance)->handle();

    $weekBalance = WeekBalance::sole();

    $weekStart = Date::parse('2025-01-06')->startOfWeek();
    $weekEnd = $weekStart->copy()->endOfWeek()->startOfSecond();

    expect($weekBalance->start_week_at->equalTo($weekStart))->toBeTrue();
    expect($weekBalance->end_week_at->equalTo($weekEnd))->toBeTrue();
    expect($weekBalance->balance)->toBe(0.0);
    expect($weekBalance->start_balance)->toBe(0);
    expect($weekBalance->end_balance)->toBe(3600);

    Date::setTestNow();
});
