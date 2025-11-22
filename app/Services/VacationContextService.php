<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AbsenceTypeEnum;
use App\Models\Absence;
use App\Models\VacationEntitlement;
use App\Settings\VacationSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class VacationContextService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $yearContextCache = [];

    public function __construct(private readonly VacationSettings $settings) {}

    /**
     * @param  Collection<int, VacationEntitlement>  $overrides
     * @return array<int, int>
     */
    public function availableYears(int $selectedYear, Collection $overrides): array
    {
        $absenceYears = Absence::query()
            ->where('type', AbsenceTypeEnum::VACATION)
            ->pluck('date')
            ->map(fn ($date): int => $date instanceof Carbon ? $date->year : Date::parse((string) $date)->year);

        return $absenceYears
            ->merge($overrides->keys())
            ->push($selectedYear)
            ->filter()
            ->map(fn ($year): int => $year)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, VacationEntitlement>  $overrides
     */
    public function minYear(int $selectedYear, Collection $overrides): int
    {
        return min($this->availableYears($selectedYear, $overrides));
    }

    /**
     * @param  Collection<int, VacationEntitlement>  $overrides
     * @return array<string, mixed>
     */
    public function yearContext(
        int $year,
        Collection $overrides,
        int $minYear,
        ?Collection $preloadedVacations = null,
        ?Carbon $referenceDate = null
    ): array {
        if (isset($this->yearContextCache[$year])) {
            if ($preloadedVacations instanceof Collection) {
                $this->yearContextCache[$year]['vacations'] = $preloadedVacations;
            }

            return $this->yearContextCache[$year];
        }

        /** @var VacationEntitlement|null $override */
        $override = $overrides->get($year);
        $days = (float) ($override?->days ?? $this->settings->default_entitlement_days);

        $carryover = $override?->carryover;
        if ($carryover === null) {
            if ($this->settings->auto_carryover && $year > $minYear) {
                $carryover = $this->yearContext($year - 1, $overrides, $minYear)['remaining'];
            } else {
                $carryover = 0.0;
            }
        } else {
            $carryover = (float) $carryover;
        }

        $vacations = $preloadedVacations ?? Absence::query()
            ->where('type', AbsenceTypeEnum::VACATION)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        $referenceDate ??= Date::create($year, 12, 31);

        $summary = VacationService::summarize($vacations, $referenceDate, $this->settings);

        $totalEntitlement = round($days + $carryover, 2);
        $remaining = round(max(0.0, $totalEntitlement - $summary['total']), 2);

        return $this->yearContextCache[$year] = [
            'days' => $days,
            'carryover' => $carryover,
            'total_entitlement' => $totalEntitlement,
            'remaining' => $remaining,
            'summary' => $summary,
            'vacations' => $vacations,
        ];
    }

    /**
     * @param  Collection<int, VacationEntitlement>  $overrides
     */
    public function calculatedCarryover(int $year, Collection $overrides, int $minYear): float
    {
        $carryover = 0.0;

        if ($this->settings->auto_carryover && $year > $minYear) {
            $carryover = $this->yearContext($year - 1, $overrides, $minYear)['remaining'];
        }

        return (float) $carryover;
    }
}
