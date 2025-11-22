<?php

declare(strict_types=1);

use App\Enums\AbsenceTypeEnum;
use App\Models\Absence;
use App\Models\VacationEntitlement;
use App\Settings\VacationSettings;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $settings = app(VacationSettings::class);
    $settings->default_entitlement_days = 30.0;
    $settings->prorate_consumption = true;
    $settings->proration_step = 0.5;
    $settings->auto_carryover = true;
    $settings->minimum_day_hours = 8.0;
    $settings->save();
});

it('opens the vacation entitlement edit modal with override data', function (): void {
    VacationEntitlement::create([
        'year' => 2026,
        'days' => 28.5,
        'carryover' => 3.0,
    ]);

    $this->get(route('absence.vacation-entitlement.edit', ['date' => '2026-05-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Absence/Vacation/Index')
            ->where('modal.component', 'Absence/Vacation/Entitlement/Edit')
            ->where('modal.props.submit_route', route('absence.vacation-entitlement.update'))
            ->where('modal.props.entitlement.year', 2026)
            ->where('modal.props.entitlement.days', 28.5)
            ->where('modal.props.entitlement.carryover', 3)
            ->where('modal.props.entitlement.defaultDays', 30)
            ->where('modal.props.entitlement.autoCarryover', true)
            ->where('modal.props.entitlement.calculatedCarryover', 0)
        );
});

it('opens the vacation entitlement edit modal with defaults when no override exists', function (): void {
    $this->get(route('absence.vacation-entitlement.edit', ['date' => '2025-01-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Absence/Vacation/Index')
            ->where('modal.component', 'Absence/Vacation/Entitlement/Edit')
            ->where('modal.props.submit_route', route('absence.vacation-entitlement.update'))
            ->where('modal.props.entitlement.year', 2025)
            ->where('modal.props.entitlement.days', null)
            ->where('modal.props.entitlement.carryover', null)
            ->where('modal.props.entitlement.defaultDays', 30)
            ->where('modal.props.entitlement.autoCarryover', true)
            ->where('modal.props.entitlement.calculatedCarryover', 0)
        );
});

it('calculates carryover from previous year when data exists', function (): void {
    Absence::create([
        'type' => AbsenceTypeEnum::VACATION,
        'date' => Date::create(2025, 6, 10),
        'duration' => 8,
    ]);

    $this->get(route('absence.vacation-entitlement.edit', ['date' => '2026-05-01']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Absence/Vacation/Index')
            ->where('modal.component', 'Absence/Vacation/Entitlement/Edit')
            ->where('modal.props.entitlement.year', 2026)
            ->where('modal.props.entitlement.calculatedCarryover', 29)
        );
});

it('removes the override when both toggles are disabled', function (): void {
    VacationEntitlement::create([
        'year' => 2027,
        'days' => 27,
        'carryover' => 2,
    ]);

    $response = $this->post(route('absence.vacation-entitlement.update'), [
        'year' => 2027,
        'days' => null,
        'carryover' => null,
    ]);

    $response->assertRedirect(route('absence.vacation.index', ['date' => '2027-01-01']));

    expect(VacationEntitlement::where('year', 2027)->exists())->toBeFalse();
});
