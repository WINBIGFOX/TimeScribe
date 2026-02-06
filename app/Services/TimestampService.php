<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TimestampTypeEnum;
use App\Events\TimerStarted;
use App\Events\TimerStopped;
use App\Jobs\CalculateWeekBalance;
use App\Models\Absence;
use App\Models\Project;
use App\Models\Timestamp;
use App\Models\WeekBalance;
use App\Models\WorkSchedule;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class TimestampService
{
    private static function start(TimestampTypeEnum $type): void
    {
        $project = null;
        if ($type === TimestampTypeEnum::WORK) {
            $project = resolve(ProjectSettings::class)->currentProject;
        }

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
            ->whereDate('started_at', '<', today())
            ->get();

        foreach ($unclosedDays as $timestamp) {
            $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
            if ($timestamp->last_ping_at->diffInMinutes(now()) < 60) {
                Timestamp::create([
                    'type' => $timestamp->type,
                    'started_at' => today(),
                    'last_ping_at' => now(),
                ]);
            }
        }

        Timestamp::whereNull('ended_at')->update(['ended_at' => now()]);
    }

    public static function startWork(): void
    {
        self::makeEndings();
        self::start(TimestampTypeEnum::WORK);
        TimerStarted::broadcast();
    }

    public static function startBreak(): void
    {
        self::makeEndings();
        self::start(TimestampTypeEnum::BREAK);
        TimerStopped::broadcast();
    }

    public static function stop(): void
    {
        self::ping();
        self::makeEndings();
        TimerStopped::broadcast();
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
                    'started_at' => today(),
                    'last_ping_at' => now(),
                ]);
                dispatch(new CalculateWeekBalance);
            }
        }
        self::createStopByOldTimestamps();
    }

    public static function checkStopTimeReset(): void
    {
        $settings = resolve(GeneralSettings::class);
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

    /**
     * Calculates the total time for a given type of timestamp between two dates.
     *
     * This method retrieves timestamp records based on the provided type, start date,
     * and end date. It also considers holidays and absences, adjusting the calculated
     * time accordingly. If no end date is provided, it defaults to the start date.
     * Additionally, the method includes an option to use the current time as a fallback
     * for ongoing timestamps.
     *
     * @param  TimestampTypeEnum  $type  The type of timestamp to calculate.
     * @param  Carbon|null  $date  The start date for the calculation. Defaults to the current date if null.
     * @param  Carbon|null  $endDate  The end date for the calculation. Defaults to the start date if null.
     * @param  bool|null  $fallbackNow  Whether to use the current time as a fallback for ongoing timestamps. Defaults to true.
     * @return float The total calculated time in seconds.
     */
    private static function getTime(TimestampTypeEnum $type, ?Carbon $date, ?Carbon $endDate = null, ?bool $fallbackNow = true, ?Project $project = null): float
    {
        if (! $date instanceof Carbon) {
            $date = Date::now();
        }
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        $timestamps = Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $endDate->endOfDay())
            ->when($project, fn ($query) => $query->where('project_id', $project->id))
            ->where('type', $type)
            ->get();

        $absenceTime = 0;

        if (! $project instanceof Project) {

            $holiday = HolidayService::getHoliday([$date->year, $endDate->year]);
            $absence = self::getAbsence($date, $endDate);

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
        }

        return $timestamps->sum(function (Timestamp $timestamp) use ($date, $fallbackNow, $project, $endDate): float {
            $fallbackTime = ($date->isToday() || $project && $endDate->isToday()) && $fallbackNow ? now() : $timestamp->last_ping_at;
            $diffTime = $timestamp->ended_at ?? $fallbackTime;

            return floor($timestamp->started_at->diff($diffTime)->totalSeconds);
        }) + $absenceTime;
    }

    public static function getWorkTime(?Carbon $date = null, ?Carbon $endDate = null, ?Project $project = null): float
    {
        return self::getTime(
            type: TimestampTypeEnum::WORK,
            date: $date,
            endDate: $endDate,
            project: $project
        );
    }

    public static function getBreakTime(?Carbon $date = null, ?Carbon $endDate = null): float
    {
        return self::getTime(
            type: TimestampTypeEnum::BREAK,
            date: $date,
            endDate: $endDate
        );
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

    public static function getTimestamps(Carbon $date, ?Carbon $endDate = null, ?Project $project = null, array $with = []): Collection
    {
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        return Timestamp::with($with)
            ->whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $endDate->endOfDay())
            ->when($project, fn ($query) => $query->where('project_id', $project->id))
            ->oldest('started_at')
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

    public static function getWorkSchedule(?Carbon $date = null): array
    {
        if (! $date instanceof Carbon) {
            $date = Date::now();
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

    public static function getDatesWithTimestamps(?Carbon $date, ?Carbon $endDate = null, ?Project $project = null): Collection
    {
        if (! $date instanceof Carbon) {
            $date = Date::now();
        }
        if (! $endDate instanceof Carbon) {
            $endDate = $date->copy();
        }

        $timestampDates = self::getTimestamps($date, $endDate, $project)->map(fn (Timestamp $timestamp) => $timestamp->started_at->format('Y-m-d'));

        if (! $project instanceof Project) {
            $holiday = HolidayService::getHoliday(range($date->year, $endDate->year))->map(fn (Carbon $holiday): string => $holiday->format('Y-m-d'));

            $absence = self::getAbsence($date, $endDate)->map(fn (Absence $absence) => $absence->date->format('Y-m-d'));

            if ($timestampDates->isEmpty()) {
                return $holiday->unique()->sort()->values();
            }

            $timestampDates = $timestampDates->merge($holiday)->merge($absence);
        }

        return $timestampDates->unique()->sort()->values();
    }

    public static function getActiveWork(Carbon $date): bool
    {
        return $date->isToday() && self::getCurrentType() === TimestampTypeEnum::WORK;
    }

    public static function getBalance(Carbon $currentDate): float
    {
        $weekBalance = WeekBalance::whereDate('start_week_at', '<=', $currentDate)->latest('start_week_at')->first();

        return $weekBalance->start_balance ?? 0;
    }

    public static function create(Carbon $date, Carbon $endDate, TimestampTypeEnum $type, ?string $description = null, ?int $projectId = null): void
    {
        if ($endDate->lessThanOrEqualTo($date)) {
            return;
        }

        $start = $date->copy();
        $end = $endDate->copy();

        $overlappingTimestamps = Timestamp::where('started_at', '<', $end)
            ->where(function ($query) use ($start): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $start);
            })
            ->oldest('started_at')
            ->get();

        foreach ($overlappingTimestamps as $timestamp) {
            $timestampEnd = $timestamp->ended_at ?? $timestamp->last_ping_at ?? $timestamp->started_at;

            if ($timestampEnd->lessThanOrEqualTo($start) || $timestamp->started_at->greaterThanOrEqualTo($end)) {
                continue;
            }

            if ($timestamp->started_at->greaterThanOrEqualTo($start) && $timestampEnd->lessThanOrEqualTo($end)) {
                $timestamp->delete();

                continue;
            }

            if ($timestamp->started_at->lessThan($start) && $timestampEnd->greaterThan($end)) {
                $timestamp->update([
                    'ended_at' => $start->copy(),
                    'last_ping_at' => $start->copy(),
                ]);

                Timestamp::create([
                    'type' => $timestamp->type,
                    'project_id' => $timestamp->project_id,
                    'description' => $timestamp->description,
                    'source' => $timestamp->source,
                    'paid' => $timestamp->paid,
                    'started_at' => $end->copy(),
                    'ended_at' => $timestampEnd->copy(),
                    'last_ping_at' => $timestampEnd->copy(),
                ]);

                continue;
            }

            if ($timestamp->started_at->lessThan($start) && $timestampEnd->greaterThan($start)) {
                $timestamp->update([
                    'ended_at' => $start->copy(),
                    'last_ping_at' => $start->copy(),
                ]);

                continue;
            }

            if ($timestamp->started_at->lessThan($end) && $timestampEnd->greaterThan($end)) {
                $updates = [
                    'started_at' => $end->copy(),
                ];

                if ($timestamp->last_ping_at instanceof Carbon && $timestamp->last_ping_at->lessThan($end)) {
                    $updates['last_ping_at'] = $end->copy();
                }

                $timestamp->update($updates);
            }
        }

        Timestamp::create([
            'type' => $type,
            'project_id' => $projectId,
            'started_at' => $start->copy(),
            'ended_at' => $end->copy(),
            'last_ping_at' => $end->copy(),
            'description' => $description,
        ]);
    }
}
