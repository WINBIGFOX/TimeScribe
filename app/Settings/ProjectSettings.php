<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ProjectSettings extends Settings
{
    public bool $showProjectTime;

    public ?int $currentProject = null;

    public ?string $defaultCurrency = null;

    public static function group(): string
    {
        return 'project';
    }
}
