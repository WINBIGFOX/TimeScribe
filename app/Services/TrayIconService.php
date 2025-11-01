<?php

declare(strict_types=1);

namespace App\Services;

use Native\Desktop\Support\Environment;

class TrayIconService
{
    public static function getIcon(?string $iconName = null): string
    {
        $isMacOs = Environment::isMac();
        $appIconPrefix = $isMacOs ? '' : 'Windows';
        $prefix = '';

        if (! $isMacOs) {
            $isLight = true;
            try {
                $isLight = str_contains(shell_exec('reg query HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\Themes\Personalize /v SystemUsesLightTheme'), '0x1');
            } catch (\Throwable) {
            }
            if (! $isLight) {
                $prefix = 'white/';
            }
        }

        return match ($iconName) {
            'work' => public_path($prefix.'WorkIconTemplate@2x.png'),
            'break' => public_path($prefix.'BreakIconTemplate@2x.png'),
            default => public_path($prefix.$appIconPrefix.'IconTemplate@2x.png')
        };
    }
}
