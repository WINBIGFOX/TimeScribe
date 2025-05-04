<?php

declare(strict_types=1);

namespace App\Services;

use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\System;
use Native\Laravel\Support\Environment;

class TrayIconService
{
    public static function getIcon(?string $iconName = null): string
    {
        $isMacOs = Environment::isMac();
        $appIconPrefix = $isMacOs ? '' : 'Windows';
        $prefix = '';

        if (! $isMacOs && System::theme() === SystemThemesEnum::DARK) {
            $prefix = 'white/';
        }

        return match ($iconName) {
            'work' => public_path($prefix.'WorkIconTemplate@2x.png'),
            'break' => public_path($prefix.'BreakIconTemplate@2x.png'),
            default => public_path($prefix.$appIconPrefix.'IconTemplate@2x.png')
        };
    }
}
