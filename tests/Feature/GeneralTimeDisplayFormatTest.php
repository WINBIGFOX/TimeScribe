<?php

declare(strict_types=1);

use App\Settings\GeneralSettings;
use Inertia\Testing\AssertableInertia as Assert;
use Native\Desktop\Facades\App;

beforeEach(function (): void {
    $this->withoutVite();
    App::shouldReceive('openAtLogin')->andReturn(false);
});

it('exposes the configured time display format in general settings', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'en_US';
    $settings->timezone = 'UTC';
    $settings->time_display_format = 'decimal';
    $settings->save();

    $this->get(route('settings.general.edit'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/General/Edit')
            ->where('timeDisplayFormat', 'decimal')
        );
});

it('updates the time display format in general settings', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'en_US';
    $settings->timezone = 'UTC';
    $settings->save();

    $this->patch(route('settings.general.update'), [
        'openAtLogin' => false,
        'theme' => 'system',
        'showTimerOnUnlock' => true,
        'holidayRegion' => null,
        'locale' => 'en_US',
        'appActivityTracking' => false,
        'timezone' => 'UTC',
        'default_overview' => 'week',
        'time_display_format' => 'decimal',
    ])->assertRedirect(route('settings.general.edit'));

    $this->get(route('settings.general.edit'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/General/Edit')
            ->where('timeDisplayFormat', 'decimal')
        );
});
