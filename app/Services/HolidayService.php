<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\CalculateWeekBalance;
use App\Models\HolidayRule;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Umulmrum\Holiday\Constant\HolidayType;
use Umulmrum\Holiday\Filter\IncludeTypeFilter;
use Umulmrum\Holiday\Formatter\DateFormatter;
use Umulmrum\Holiday\HolidayCalculator;

class HolidayService
{
    public static function addHoliday(Carbon $date): void
    {
        $negativeHoliday = HolidayRule::whereDate('date', $date)->where('is_holiday', false)->first();

        if ($negativeHoliday) {
            $negativeHoliday->delete();
        } else {
            HolidayRule::updateOrCreate(
                ['date' => $date],
                ['is_holiday' => true]
            );
        }

        dispatch(new CalculateWeekBalance);
    }

    public static function removeHoliday(Carbon $date): void
    {
        $holiday = HolidayRule::whereDate('date', $date)->where('is_holiday', true)->first();

        if ($holiday) {
            $holiday->delete();
        } else {
            HolidayRule::updateOrCreate(
                ['date' => $date],
                ['is_holiday' => false]
            );
        }

        dispatch(new CalculateWeekBalance);
    }

    private static function mergeHolidays(Collection $dates): Collection
    {
        $dates = $dates->sort()->unique()->values();
        $firstDate = $dates->first();
        $lastDate = $dates->last();

        $holidays = HolidayRule::whereBetween('date', [$firstDate, $lastDate])->get();

        $removeHolidays = $holidays->filter(fn ($holiday): bool => ! $holiday->is_holiday);

        $addHolidays = $holidays->filter(fn ($holiday) => $holiday->is_holiday);

        $datesToAdd = $addHolidays->pluck('date')->diff($dates);
        $datesToRemove = $removeHolidays->pluck('date')->intersect($dates);
        $dates = $dates->diff($datesToRemove)->merge($datesToAdd);

        return $dates->sort()->unique()->values();
    }

    public static function getHoliday(int|array $year): Collection
    {
        $settings = resolve(GeneralSettings::class);
        if ($settings->holidayRegion === null) {
            return collect();
        }
        $holidayCalculator = new HolidayCalculator;

        return self::mergeHolidays(collect(
            $holidayCalculator->calculate($settings->holidayRegion, $year)
                ->filter(new IncludeTypeFilter(HolidayType::DAY_OFF))
                ->format(new DateFormatter)
        )->map(fn ($holiday): ?Carbon => Date::create($holiday)));
    }

    public static function isHoliday(Carbon $date): bool
    {
        return self::getHoliday($date->year)->contains($date);
    }
}
