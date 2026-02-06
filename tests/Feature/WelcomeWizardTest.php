<?php

declare(strict_types=1);

use App\Settings\VacationSettings;
use Inertia\Testing\AssertableInertia as Assert;
use Native\Desktop\Facades\App;

beforeEach(function (): void {
    App::shouldReceive('openAtLogin')->andReturn(false);
});

it('exposes vacation settings in the welcome wizard', function (): void {
    $vacationSettings = resolve(VacationSettings::class);
    $vacationSettings->default_entitlement_days = 30;
    $vacationSettings->auto_carryover = true;
    $vacationSettings->minimum_day_hours = 6.5;
    $vacationSettings->save();

    $this->get(route('welcome.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Welcome/Index')
            ->where('vacationSettings.default_entitlement_days', 30)
            ->where('vacationSettings.auto_carryover', true)
            ->where('vacationSettings.minimum_day_hours', 6.5)
        );
});

it('updates vacation settings and derives minimum hours from the work schedule', function (): void {
    $this->patch(route('welcome.update'), [
        'workSchedule' => [
            'sunday' => 0,
            'monday' => 8,
            'tuesday' => 6,
            'wednesday' => 6,
            'thursday' => 8,
            'friday' => 4,
            'saturday' => 0,
        ],
        'vacation' => [
            'default_entitlement_days' => 28,
            'auto_carryover' => true,
        ],
    ])->assertOk();

    /** @var VacationSettings $vacationSettings */
    $vacationSettings = resolve(VacationSettings::class);
    $vacationSettings->refresh();

    expect($vacationSettings->default_entitlement_days)->toBe(28.0);
    expect($vacationSettings->auto_carryover)->toBeTrue();
    expect($vacationSettings->minimum_day_hours)->toBe(8.0);
});
