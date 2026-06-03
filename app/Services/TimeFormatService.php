<?php

declare(strict_types=1);

namespace App\Services;

class TimeFormatService
{
    public const string CLOCK = 'clock';

    public const string DECIMAL = 'decimal';

    public static function formatDuration(int|float $seconds, string $format = self::CLOCK): string
    {
        if ($format === self::DECIMAL) {
            return number_format($seconds / 3600, 2, '.', '');
        }

        return gmdate('G:i', (int) $seconds);
    }

    public static function unitTranslationKey(int|float $seconds, string $format = self::CLOCK): string
    {
        if ($format === self::DECIMAL) {
            return 'h';
        }

        return abs($seconds) >= 3600 ? 'h' : 'min';
    }

    public static function formatDurationWithUnit(int|float $seconds, string $format = self::CLOCK): string
    {
        $unit = self::unitTranslationKey($seconds, $format);

        if (app()->bound('translator')) {
            $unit = trans('app.'.$unit);
        }

        $formattedDuration = self::formatDuration($seconds, $format);

        if ($format === self::CLOCK && abs($seconds) < 3600) {
            $minutes = (int) floor(abs($seconds) / 60);
            $formattedDuration = ($seconds < 0 ? '-' : '').$minutes;
        }

        return sprintf(
            '%s %s',
            $formattedDuration,
            $unit
        );
    }
}
