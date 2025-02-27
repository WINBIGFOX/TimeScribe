<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TimestampTypeEnum;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Jobs\CalculateWeekBalance;
use App\Models\Absence;
use App\Models\Timestamp;
use App\Models\WeekBalance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Native\Laravel\Facades\Settings;
use Umulmrum\Holiday\Constant\HolidayType;
use Umulmrum\Holiday\Filter\IncludeTypeFilter;
use Umulmrum\Holiday\Formatter\DateFormatter;
use Umulmrum\Holiday\HolidayCalculator;

class TimestampService
{
    private static function create(TimestampTypeEnum $type): void
    {
        Timestamp::create([
            'type' => $type,
            'started_at' => now(),
            'last_ping_at' => now(),
        ]);
    }

    private static function makeEndings(): void
    {
        $unclosedDays = Timestamp::whereNull('ended_at')
            ->whereDate('started_at', '<', now()->startOfDay())
            ->get();

        foreach ($unclosedDays as $timestamp) {
            $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
            if ($timestamp->last_ping_at->diffInMinutes(now()) < 60) {
                Timestamp::create([
                    'type' => $timestamp->type,
                    'started_at' => now()->startOfDay(),
                    'last_ping_at' => now(),
                ]);
            }
        }

        Timestamp::whereNull('ended_at')->update(['ended_at' => now()]);
    }

    public static function startWork(): void
    {
        TimerStarted::broadcast();
        self::makeEndings();
        self::create(TimestampTypeEnum::WORK);
    }

    public static function startBreak(): void
    {
        TimerStopped::broadcast();
        self::makeEndings();
        self::create(TimestampTypeEnum::BREAK);
    }

    public static function stop(): void
    {
        TimerStopped::broadcast();
        self::ping();
        self::makeEndings();
    }

    public static function ping(): void
    {
        self::checkStopTimeReset();

        $activeTimestamps = Timestamp::whereNull('ended_at')
            ->where('last_ping_at', '>=', now()->subHours(8))->get();

        foreach ($activeTimestamps as $timestamp) {
            $timestamp->update(['last_ping_at' => now()]);

            if ($timestamp->started_at->isYesterday()) {
                $timestamp->update(['ended_at' => $timestamp->started_at->endOfDay()]);
                Timestamp::create([
                    'type' => $timestamp->type,
                    'started_at' => now()->startOfDay(),
                    'last_ping_at' => now(),
                ]);
                CalculateWeekBalance::dispatch();
            }
        }
        self::createStopByOldTimestamps();
    }

    public static function checkStopTimeReset(): void
    {
        $workTimeReset = Settings::get('stopWorkTimeReset');
        $breakTimeReset = Settings::get('stopBreakTimeReset');

        $activeTimestamps = Timestamp::whereNull('ended_at')->get();

        foreach ($activeTimestamps as $timestamp) {
            if ($workTimeReset && $workTimeReset > 0 && $timestamp->type === TimestampTypeEnum::WORK) {
                if ($timestamp->last_ping_at->diffInMinutes(now()) >= $workTimeReset) {
                    $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
                }
            }
            if ($breakTimeReset && $breakTimeReset > 0 && $timestamp->type === TimestampTypeEnum::BREAK) {
                if ($timestamp->last_ping_at->diffInMinutes(now()) >= $breakTimeReset) {
                    $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
                }
            }
        }
    }

    private static function createStopByOldTimestamps(): void
    {
        $oldTimestamps = Timestamp::whereNull('ended_at')
            ->where('last_ping_at', '<', now()->subHour())->get();

        foreach ($oldTimestamps as $timestamp) {
            $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
        }
    }

    private static function getTime(TimestampTypeEnum $type, ?Carbon $date, ?Carbon $endDate = null, ?bool $fallbackNow = true): float
    {
        if (! $date) {
            $date = Carbon::now();
        }
        if (! $endDate) {
            $endDate = $date->copy();
        }

        $holiday = self::getHoliday([$date->year, $endDate->year]);
        $absence = self::getAbsence($date, $endDate);
        $workdays = Settings::get('workdays', []);

        $timestamps = Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $endDate->endOfDay())
            ->where('type', $type)
            ->get();

        $absenceTime = 0;

        $periode = CarbonPeriod::create($date, $endDate);

        if ($type === TimestampTypeEnum::WORK) {
            foreach ($periode as $rangeDate) {
                if (
                    $holiday->filter(fn (Carbon $holiday) => $holiday->isSameDay($rangeDate))->isNotEmpty() ||
                    $absence->filter(fn (Absence $absence) => $absence->date->isSameDay($rangeDate))->isNotEmpty()
                ) {
                    $absenceTime += ($workdays[strtolower($rangeDate->locale('en')->dayName)] ?? 0) * 60 * 60;
                }
            }
        }

        return $timestamps->sum(function (Timestamp $timestamp) use ($date, $fallbackNow) {
            if ($date->isToday() && $fallbackNow) {
                $fallbackTime = now();
            } else {
                $fallbackTime = $timestamp->last_ping_at;
            }
            $diffTime = $timestamp->ended_at ?? $fallbackTime;

            return $timestamp->started_at->diff($diffTime)->totalSeconds;
        }) + $absenceTime;
    }

    public static function getWorkTime(?Carbon $date = null, ?Carbon $endDate = null): float
    {
        return self::getTime(TimestampTypeEnum::WORK, $date, $endDate);
    }

    public static function getBreakTime(?Carbon $date = null, ?Carbon $endDate = null): float
    {
        return self::getTime(TimestampTypeEnum::BREAK, $date, $endDate);
    }

    public static function getNoWorkTime(?Carbon $date = null): float
    {
        $timestamps = self::getTimestamps($date);

        if ($timestamps->isEmpty()) {
            return 0;
        }

        $firstWorkTimestamp = $timestamps->firstWhere('type', TimestampTypeEnum::WORK);

        if (! $firstWorkTimestamp) {
            return 0;
        }

        $lastWorkTimestamp = $timestamps->last();

        $workTimeRange = $firstWorkTimestamp->started_at->diffInSeconds($lastWorkTimestamp->ended_at ?? $lastWorkTimestamp->last_ping_at);

        $workTime = self::getTime(TimestampTypeEnum::WORK, $date, null, false);

        return max($workTimeRange - $workTime, 0);
    }

    public static function getCurrentType(): ?TimestampTypeEnum
    {
        return Timestamp::whereNull('ended_at')->first()?->type;
    }

    public static function getTimestamps(Carbon $date, ?Carbon $endDate = null, ?bool $withEditAttributes = false): Collection
    {
        if (! $endDate) {
            $endDate = $date->copy();
        }

        return Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $endDate->endOfDay())
            ->orderBy('started_at')
            ->get()
            ->append($withEditAttributes ? ['can_start_edit', 'can_end_edit'] : []);
    }

    public static function getAbsence(Carbon $date, ?Carbon $endDate = null): Collection
    {
        if (! $endDate) {
            $endDate = $date->copy();
        }

        return Absence::whereBetween('date', [$date->startOfDay(), $endDate->endOfDay()])
            ->orderBy('date')
            ->get();
    }

    public static function getHoliday(int|array $year): Collection
    {
        if (Settings::get('holidayRegion') === null) {
            return collect();
        }
        $holidayCalculator = new HolidayCalculator;

        return collect(
            $holidayCalculator->calculate(Settings::get('holidayRegion'), $year)
                ->filter(new IncludeTypeFilter(HolidayType::OFFICIAL))
                ->format(new DateFormatter)
        )->map(function ($holiday) {
            return Carbon::create($holiday);
        });
    }

    public static function getPlan($dayName): ?float
    {
        $workdays = Settings::get('workdays', []);

        return $workdays[$dayName] ?? 0;
    }

    public static function getWeekPlan(): ?float
    {
        $workdays = Settings::get('workdays', []);

        return array_sum($workdays);
    }

    public static function getFallbackPlan(?Carbon $date = null, ?Carbon $endDate = null): ?float
    {
        $workTime = self::getWorkTime($date, $endDate) / 3600;

        $workdays = collect(Settings::get('workdays', []))->values()->unique()->sort();

        return $workdays->filter(fn ($value) => $value >= $workTime)->first() ?? $workdays->last();
    }

    public static function getDatesWithTimestamps(?Carbon $date, ?Carbon $endDate = null): Collection
    {
        if (! $date) {
            $date = Carbon::now();
        }
        if (! $endDate) {
            $endDate = $date->copy();
        }
        $holiday = self::getHoliday(range($date->year, $endDate->year))->map(function (Carbon $holiday) {
            return $holiday->format('Y-m-d');
        });

        $absence = self::getAbsence($date, $endDate)->map(function (Absence $absence) {
            return $absence->date->format('Y-m-d');
        });

        $timestampDates = self::getTimestamps($date, $endDate)->map(function (Timestamp $timestamp) {
            return $timestamp->started_at->format('Y-m-d');
        });

        if ($timestampDates->isEmpty()) {
            return $holiday->unique()->sort()->values();
        }

        return $timestampDates->merge($holiday)->merge($absence)->unique()->sort()->values();
    }

    public static function getActiveWork(Carbon $date): bool
    {
        return $date->isToday() && self::getCurrentType() === TimestampTypeEnum::WORK;
    }

    public static function getBalance(Carbon $currentDate): float
    {
        return WeekBalance::where('end_week_at', '<', $currentDate->startOfWeek())
            ->sum('balance') ?? 0;
    }
}
