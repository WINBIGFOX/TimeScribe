<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FlyTimerSettings extends Settings
{
    public bool $showWithStart = false;

    public static function group(): string
    {
        return 'fly_timer';
    }
}
