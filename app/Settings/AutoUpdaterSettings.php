<?php

declare(strict_types=1);

namespace App\Settings;

use Carbon\Carbon;
use Spatie\LaravelSettings\Settings;

class AutoUpdaterSettings extends Settings
{
    public bool $autoUpdate;

    public ?Carbon $lastCheck = null;

    public ?string $lastVersion = null;

    public bool $isDownloaded;

    public static function group(): string
    {
        return 'auto_updater';
    }
}
