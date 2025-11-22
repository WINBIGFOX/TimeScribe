<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VacationSettings extends Settings
{
    public float $default_entitlement_days;

    public bool $prorate_consumption;

    public ?float $proration_step = null;

    public bool $auto_carryover;

    public float $minimum_day_hours = 8;

    public static function group(): string
    {
        return 'vacation';
    }
}
