<?php

declare(strict_types=1);

use App\Enums\AbsenceTypeEnum;
use App\Models\Absence;
use App\Models\VacationEntitlement;
use App\Models\WorkSchedule;
use App\Settings\VacationSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Cache::clear();

    $settings = resolve(VacationSettings::class);
    $settings->default_entitlement_days = 30.0;
    $settings->prorate_consumption = true;
    $settings->proration_step = 0.5;
    $settings->auto_carryover = false;
    $settings->minimum_day_hours = 8.0;
    $settings->save();
});

afterEach(function (): void {
    Date::setTestNow();
});

it('renders the vacation overview with calculated summary data', function (): void {
    Date::setTestNow(Date::create(2025, 3, 1));

    WorkSchedule::create([
        'valid_from' => Date::create(2024, 1, 1),
        'sunday' => 0,
        'monday' => 8,
        'tuesday' => 8,
        'wednesday' => 8,
        'thursday' => 8,
        'friday' => 8,
        'saturday' => 0,
    ]);

    Absence::create([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 2, 3),
        'duration' => null,
    ]);

    Absence::create([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2024, 12, 20),
        'duration' => 8,
    ]);

    Absence::create([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 2, 4),
        'duration' => 4,
    ]);

    Absence::create([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 8, 12),
        'duration' => null,
    ]);

    VacationEntitlement::create([
        'year' => 2025,
        'days' => 30,
        'carryover' => 2,
    ]);

    $this->get(route('absence.vacation.index', ['date' => '2025-01-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Absence/Vacation/Index')
            ->where('date', '2025-01-01')
            ->where('summary.taken', 1.5)
            ->where('summary.planned', 1)
            ->where('summary.consumed', 2.5)
            ->where('summary.totalEntitlement', 32)
            ->where('summary.remaining', 29.5)
            ->where('entitlement.year', 2025)
            ->where('entitlement.days', 30)
            ->where('entitlement.carryover', 2)
            ->where('entitlement.defaultDays', 30)
            ->where('entitlement.autoCarryover', false)
            ->has('entries', 3)
            ->where('entries.0.status', 'planned')
            ->where('entries.0.day_equivalent', 1)
            ->where('entries.1.status', 'taken')
            ->where('entries.1.day_equivalent', 0.5)
            ->where('entries.2.status', 'taken')
            ->where('entries.2.day_equivalent', 1)
            ->where('availableYears', [2024, 2025])
        );
});

it('stores or updates a vacation entitlement', function (): void {
    $response = $this->post(route('absence.vacation-entitlement.update'), [
        'year' => 2026,
        'days' => 30,
        'carryover' => 1.0,
    ]);

    $response->assertRedirect(route('absence.vacation.index', ['date' => '2026-01-01']));

    $entitlement = VacationEntitlement::where('year', 2026)->first();

    expect($entitlement)->not->toBeNull();
    expect($entitlement?->days)->toBe(30.0);
    expect($entitlement?->carryover)->toBe(1.0);
});

it('updates vacation settings', function (): void {
    $response = $this->patch(route('settings.vacation.update'), [
        'default_entitlement_days' => 28,
        'prorate_consumption' => false,
        'proration_step' => 0.5,
        'auto_carryover' => true,
        'minimum_day_hours' => 6.5,
    ]);

    $response->assertRedirect(route('settings.vacation.edit'));

    $settings = resolve(VacationSettings::class);
    $settings->refresh();

    expect($settings->default_entitlement_days)->toBe(28.0);
    expect($settings->prorate_consumption)->toBeFalse();
    expect($settings->proration_step)->toBe(0.5);
    expect($settings->auto_carryover)->toBeTrue();
    expect($settings->minimum_day_hours)->toBe(6.5);
});

it('allows negative remaining days and carries them over automatically', function (): void {
    Date::setTestNow(Date::create(2025, 1, 15));

    $settings = resolve(VacationSettings::class);
    $settings->auto_carryover = true;
    $settings->save();

    WorkSchedule::create([
        'valid_from' => Date::create(2024, 1, 1),
        'sunday' => 0,
        'monday' => 8,
        'tuesday' => 8,
        'wednesday' => 8,
        'thursday' => 8,
        'friday' => 8,
        'saturday' => 0,
    ]);

    VacationEntitlement::create([
        'year' => 2024,
        'days' => 10,
    ]);

    foreach ([1, 2, 3, 4, 5, 8, 9, 10, 11, 12, 15, 16] as $day) {
        Absence::create([
            'type' => AbsenceTypeEnum::VACATION,
            'date' => Date::create(2024, 1, $day),
            'duration' => null,
        ]);
    }

    $this->get(route('absence.vacation.index', ['date' => '2024-01-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.totalEntitlement', 10)
            ->where('summary.remaining', -2)
        );

    $this->get(route('absence.vacation.index', ['date' => '2025-01-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.totalEntitlement', 28)
            ->where('summary.remaining', 28)
            ->where('entitlement.autoCarryover', true)
        );
});
