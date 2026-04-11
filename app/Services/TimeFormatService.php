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
}
