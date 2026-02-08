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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    Cache::clear();

    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'en_US';
    $settings->timezone = 'UTC';
    $settings->save();
});

afterEach(function (): void {
    Date::setTestNow();
});

it('calculates weekly balances with relative adjustments', function (): void {
    Date::setTestNow(Date::parse('2025-01-08 12:00:00'));

    createStandardWorkSchedule();

    createWorkTimestamps([
        '2025-01-06',
        '2025-01-07',
        '2025-01-08',
        '2025-01-09',
        '2025-01-10',
    ]);

    OvertimeAdjustment::create([
        'effective_date' => Date::parse('2025-01-08'),
        'type' => OvertimeAdjustmentTypeEnum::Relative,
        'seconds' => 3600,
        'note' => 'carryover',
    ]);

    (new CalculateWeekBalance)->handle();

    $weekStart = Date::parse('2025-01-06')->startOfWeek();
    $weekEnd = $weekStart->copy()->endOfWeek()->startOfSecond();
    $weekBalance = WeekBalance::where('start_week_at', $weekStart)->sole();

    expect($weekBalance->start_week_at->equalTo($weekStart))->toBeTrue();
    expect($weekBalance->end_week_at->equalTo($weekEnd))->toBeTrue();
    expect($weekBalance->balance)->toBe(0.0);
    expect($weekBalance->start_balance)->toBe(0);
    expect($weekBalance->end_balance)->toBe(3600);
});

it('clears week balances when no timestamps exist', function (): void {
    Date::setTestNow(Date::parse('2025-01-08 12:00:00'));

    $weekStart = Date::parse('2025-01-06')->startOfWeek();

    WeekBalance::create([
        'start_week_at' => $weekStart,
        'end_week_at' => $weekStart->copy()->endOfWeek()->startOfSecond(),
        'balance' => 1200,
        'start_balance' => 0,
        'end_balance' => 1200,
    ]);

    (new CalculateWeekBalance)->handle();

    expect(WeekBalance::count())->toBe(0);
});

it('applies absolute adjustments when present in the week', function (): void {
    Date::setTestNow(Date::parse('2025-01-08 12:00:00'));

    createStandardWorkSchedule();
    createWorkTimestamps([
        '2025-01-06',
        '2025-01-07',
        '2025-01-08',
        '2025-01-09',
        '2025-01-10',
    ]);

    OvertimeAdjustment::create([
        'effective_date' => Date::parse('2025-01-08'),
        'type' => OvertimeAdjustmentTypeEnum::Absolute,
        'seconds' => 7200,
        'note' => 'reset',
    ]);

    (new CalculateWeekBalance)->handle();

    $weekBalance = WeekBalance::orderBy('start_week_at')->firstOrFail();

    expect($weekBalance->balance)->toBe(0.0);
    expect($weekBalance->start_balance)->toBe(0);
    expect($weekBalance->end_balance)->toBe(7200);
});

it('carries balances across multiple weeks', function (): void {
    Date::setTestNow(Date::parse('2025-01-08 12:00:00'));

    createStandardWorkSchedule();
    createWorkTimestamps([
        '2025-01-06',
        '2025-01-07',
        '2025-01-08',
        '2025-01-09',
        '2025-01-10',
        '2025-01-13',
        '2025-01-14',
        '2025-01-15',
        '2025-01-16',
        '2025-01-17',
    ]);

    OvertimeAdjustment::create([
        'effective_date' => Date::parse('2025-01-08'),
        'type' => OvertimeAdjustmentTypeEnum::Relative,
        'seconds' => 3600,
        'note' => 'carryover',
    ]);

    (new CalculateWeekBalance)->handle();

    $balances = WeekBalance::orderBy('start_week_at')->get();

    expect($balances)->toHaveCount(2);

    $firstWeek = $balances->first();
    $secondWeek = $balances->last();
    $expectedSecondWeekStart = $firstWeek->start_week_at->copy()->addWeek();

    expect($secondWeek->start_week_at->equalTo($expectedSecondWeekStart))->toBeTrue();
    expect($firstWeek->end_balance)->toBe(3600);
    expect($secondWeek->start_balance)->toBe(3600);
    expect($secondWeek->balance)->toBe(0.0);
    expect($secondWeek->end_balance)->toBe(3600);
});

function createStandardWorkSchedule(): void
{
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
}

function createWorkTimestamps(array $workDays): void
{
    foreach ($workDays as $workDay) {
        $start = Date::parse($workDay.' 09:00:00');
        $end = Date::parse($workDay.' 17:00:00');

        Timestamp::create([
            'type' => TimestampTypeEnum::WORK,
            'started_at' => $start,
            'ended_at' => $end,
            'last_ping_at' => $end,
        ]);
    }
}
