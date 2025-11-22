<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AbsenceTypeEnum;
use App\Models\Absence;
use App\Settings\VacationSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class VacationService
{
    private static function averagePlannedHours(Absence $absence): float
    {
        return collect(TimestampService::getWorkSchedule($absence->date))
            ->filter(fn (?float $value): bool => (float) $value > 0)
            ->avg() ?? 0.0;
    }

    /**
     * @return array{hours: float, days: float, plan_hours: float|null, status: 'taken'|'planned'}
     */
    public static function metricsForAbsence(
        Absence $absence,
        ?Carbon $referenceDate = null,
        ?VacationSettings $settings = null
    ): array {
        if ($absence->type !== AbsenceTypeEnum::VACATION) {
            return [
                'hours' => 0.0,
                'days' => 0.0,
                'plan_hours' => TimestampService::getPlan($absence->date),
                'status' => 'planned',
            ];
        }

        $referenceDate ??= Date::now();
        $settings ??= app(VacationSettings::class);

        $planHours = TimestampService::getPlan($absence->date);
        $averagePlanHours = self::averagePlannedHours($absence);
        $minimumDayHours = max(0.0, $settings->minimum_day_hours);

        $hours = (float) ($absence->duration ?? $planHours ?? 0.0);
        $divisor = $planHours ?: $averagePlanHours;

        if ($divisor <= 0.0 && $hours > 0.0) {
            $divisor = $hours;
        }

        $days = 0.0;

        if ($hours > 0.0) {
            if ($settings->prorate_consumption) {
                $effectiveDivisor = max($divisor, $minimumDayHours);

                if ($effectiveDivisor <= 0.0) {
                    $effectiveDivisor = $hours;
                }

                $rawDays = $effectiveDivisor > 0.0 ? $hours / $effectiveDivisor : 1.0;
                $step = max(0.25, $settings->proration_step);
                $days = round($rawDays / $step) * $step;
            } else {
                $days = 1.0;
            }
        }

        $days = min(1.0, max(0.0, $days));

        return [
            'hours' => $hours,
            'days' => $days,
            'plan_hours' => $planHours,
            'status' => $absence->date->lessThanOrEqualTo($referenceDate) ? 'taken' : 'planned',
        ];
    }

    /**
     * @param  Collection<int, Absence>  $absences
     * @return array{taken: float, planned: float, total: float}
     */
    public static function summarize(
        Collection $absences,
        ?Carbon $referenceDate = null,
        ?VacationSettings $settings = null
    ): array {
        $settings ??= app(VacationSettings::class);

        $metrics = $absences
            ->filter(fn (Absence $absence): bool => $absence->type === AbsenceTypeEnum::VACATION)
            ->map(fn (Absence $absence): array => self::metricsForAbsence($absence, $referenceDate, $settings));

        $taken = $metrics->where('status', 'taken')->sum('days');
        $planned = $metrics->where('status', 'planned')->sum('days');

        return [
            'taken' => round($taken, 2),
            'planned' => round($planned, 2),
            'total' => round($taken + $planned, 2),
        ];
    }
}
