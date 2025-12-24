<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShortcutSettings extends Settings
{
    public ?string $startShortcut = null;

    public ?string $stopShortcut = null;

    public ?string $pauseShortcut = null;

    public ?string $overviewShortcut = null;

    public ?string $projectPickerShortcut = null;

    public static function group(): string
    {
        return 'shortcuts';
    }
}
