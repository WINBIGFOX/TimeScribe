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
use App\Models\WorkSchedule;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Umulmrum\Holiday\Constant\HolidayType;
use Umulmrum\Holiday\Filter\IncludeTypeFilter;
use Umulmrum\Holiday\Formatter\DateFormatter;
use Umulmrum\Holiday\HolidayCalculator;

class TimestampService
{
    private static function create(TimestampTypeEnum $type): void
    {
        $project = app(ProjectSettings::class)->currentProject;
        Timestamp::create([
            'type' => $type,
            'project_id' => $project,
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
                $endedAt = $timestamp->started_at->copy()->endOfDay();
                $timestamp->update([
                    'ended_at' => $endedAt,
                    'last_ping_at' => $endedAt,
                ]);
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
        $settings = app(GeneralSettings::class);
        $workTimeReset = $settings->stopWorkTimeReset;
        $breakTimeReset = $settings->stopBreakTimeReset;

        $activeTimestamps = Timestamp::whereNull('ended_at')->get();

        foreach ($activeTimestamps as $timestamp) {
            if ($workTimeReset && $workTimeReset > 0 && $timestamp->type === TimestampTypeEnum::WORK && $timestamp->last_ping_at->diffInMinutes(now()) >= $workTimeReset) {
                $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
            }
            if ($breakTimeReset && $breakTimeReset > 0 && $timestamp->type === TimestampTypeEnum::BREAK && $timestamp->last_ping_at->diffInMinutes(now()) >= $breakTimeReset) {
                $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
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
        if (! $date instanceof Carbon) {
            $date = Carbon::now();
        }
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        $holiday = self::getHoliday([$date->year, $endDate->year]);
        $absence = self::getAbsence($date, $endDate);

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
                    $absenceTime += (self::getPlan($rangeDate)) * 60 * 60;
                }
            }
        }

        return $timestamps->sum(function (Timestamp $timestamp) use ($date, $fallbackNow) {
            $fallbackTime = $date->isToday() && $fallbackNow ? now() : $timestamp->last_ping_at;
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

    public static function getTimestamps(Carbon $date, ?Carbon $endDate = null): Collection
    {
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        return Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $endDate->endOfDay())
            ->orderBy('started_at')
            ->get();
    }

    public static function getAbsence(Carbon $date, ?Carbon $endDate = null): Collection
    {
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        return Absence::whereBetween('date', [$date->startOfDay(), $endDate->endOfDay()])
            ->orderBy('date')
            ->get();
    }

    public static function getHoliday(int|array $year): Collection
    {
        $settings = app(GeneralSettings::class);
        if ($settings->holidayRegion === null) {
            return collect();
        }
        $holidayCalculator = new HolidayCalculator;

        return collect(
            $holidayCalculator->calculate($settings->holidayRegion, $year)
                ->filter(new IncludeTypeFilter(HolidayType::DAY_OFF))
                ->format(new DateFormatter)
        )->map(fn ($holiday): ?Carbon => Carbon::create($holiday));
    }

    public static function getWorkSchedule(?Carbon $date = null): array
    {
        if (! $date instanceof Carbon) {
            $date = Carbon::now();
        }

        return Cache::flexible($date->format('Y-m-d'), [10, 0], function () use ($date) {
            $workdays = [
                'sunday' => 0,
                'monday' => 0,
                'tuesday' => 0,
                'wednesday' => 0,
                'thursday' => 0,
                'friday' => 0,
                'saturday' => 0,
            ];

            $startOfWeek = $date->clone()->startOfWeek();
            $endOfWeek = $date->clone()->endOfWeek();

            $workSchedules = WorkSchedule::whereDate('valid_from', '<=', $endOfWeek)
                ->orderByDesc('valid_from')->get();

            $newWorkSchedules = collect();
            foreach ($workSchedules as $workSchedule) {
                $newWorkSchedules->push($workSchedule);
                if ($workSchedule->valid_from->isBefore($startOfWeek)) {
                    break;
                }
            }
            $workSchedules = $newWorkSchedules->sortByDesc('valid_from');

            if ($workSchedules->count() === 1) {
                $workdays = $workSchedules->first()->only([
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                ]);
            } else {
                $datePeriod = CarbonPeriod::create($startOfWeek, $endOfWeek);
                foreach ($datePeriod as $date) {
                    $dayName = strtolower((string) $date->locale('en')->dayName);

                    $schedule = $workSchedules->firstWhere(fn (WorkSchedule $item): bool => $item->valid_from <= $date);
                    $workdays[$dayName] = $schedule ? $schedule->{$dayName} : 0;
                }
            }

            return $workdays;
        });
    }

    public static function getPlan(Carbon $date): ?float
    {
        $workdays = self::getWorkSchedule($date);

        return $workdays[strtolower($date->englishDayOfWeek)] ?? 0;
    }

    public static function getWeekPlan(?Carbon $date = null): ?float
    {
        $workdays = self::getWorkSchedule($date);

        return array_sum($workdays);
    }

    public static function getFallbackPlan(?Carbon $date = null, ?Carbon $endDate = null): ?float
    {
        $workTime = self::getWorkTime($date, $endDate) / 3600;

        $workdays = collect(self::getWorkSchedule($date))->values()->unique()->sort();

        return $workdays->filter(fn ($value): bool => $value >= $workTime)->first() ?? $workdays->last();
    }

    public static function getDatesWithTimestamps(?Carbon $date, ?Carbon $endDate = null): Collection
    {
        if (! $date instanceof Carbon) {
            $date = Carbon::now();
        }
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }
        $holiday = self::getHoliday(range($date->year, $endDate->year))->map(fn (Carbon $holiday): string => $holiday->format('Y-m-d'));

        $absence = self::getAbsence($date, $endDate)->map(fn (Absence $absence) => $absence->date->format('Y-m-d'));

        $timestampDates = self::getTimestamps($date, $endDate)->map(fn (Timestamp $timestamp) => $timestamp->started_at->format('Y-m-d'));

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
