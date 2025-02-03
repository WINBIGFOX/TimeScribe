<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class DateHelper
{
    public static function toResourceArray(?Carbon $date, bool $withTime = false, string $format = 'd.m.Y'): ?array
    {
        if ($date === null) {
            return null;
        }

        if ($withTime) {
            $format = 'd.m.Y H:i:s';
        }

        return [
            'diff' => $date->locale('de')->diffForHumans(options: CarbonInterface::JUST_NOW),
            'formatted' => $date->format($format),
            'date' => $date->format('Y-m-d'.($withTime ? ' H:i:s' : '')),
            'day' => $date->format('j'),
        ];
    }
}
