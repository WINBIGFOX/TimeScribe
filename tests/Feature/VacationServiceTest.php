<?php

declare(strict_types=1);

use App\Enums\AbsenceTypeEnum;
use App\Models\Absence;
use App\Models\WorkSchedule;
use App\Services\VacationService;
use App\Settings\VacationSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    Cache::clear();

    Date::setTestNow(Date::create(2025, 1, 15));

    $settings = app(VacationSettings::class);
    $settings->default_entitlement_days = 30.0;
    $settings->prorate_consumption = true;
    $settings->proration_step = 0.25;
    $settings->auto_carryover = false;
    $settings->minimum_day_hours = 8.0;
    $settings->save();
});

afterEach(function (): void {
    Date::setTestNow();
});

it('uses the configured minimum day hours when scheduled hours are lower', function (): void {
    WorkSchedule::create([
        'valid_from' => Date::create(2024, 1, 1),
        'sunday' => 0,
        'monday' => 4,
        'tuesday' => 4,
        'wednesday' => 4,
        'thursday' => 4,
        'friday' => 4,
        'saturday' => 0,
    ]);

    $absence = Absence::make([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 1, 16),
        'duration' => 4,
    ]);

    $metrics = VacationService::metricsForAbsence($absence);

    expect($metrics['hours'])->toBe(4.0);
    expect($metrics['days'])->toBe(0.5);
});

it('honors custom minimum day hours values', function (): void {
    $settings = app(VacationSettings::class);
    $settings->minimum_day_hours = 6.0;
    $settings->save();

    WorkSchedule::create([
        'valid_from' => Date::create(2024, 1, 1),
        'sunday' => 0,
        'monday' => 4,
        'tuesday' => 4,
        'wednesday' => 4,
        'thursday' => 4,
        'friday' => 4,
        'saturday' => 0,
    ]);

    $absence = Absence::make([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 1, 16),
        'duration' => 4,
    ]);

    $metrics = VacationService::metricsForAbsence($absence);

    expect($metrics['days'])->toBe(0.75);
});
