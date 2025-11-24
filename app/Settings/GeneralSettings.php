<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $id;

    public string $locale;

    public ?string $timezone = null;

    public bool $showTimerOnUnlock;

    public ?string $holidayRegion = null;

    public ?string $stopBreakAutomatic = null;

    public ?string $stopBreakAutomaticActivationTime = null;

    public ?int $stopWorkTimeReset = null;

    public ?int $stopBreakTimeReset = null;

    public bool $appActivityTracking;

    public bool $wizard_completed;

    public string $theme;

    public string $default_overview = 'week';

    public static function group(): string
    {
        return 'general';
    }
}
