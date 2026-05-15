<?php

declare(strict_types=1);

namespace App\Enums;

enum TimestampTypeEnum: string
{
    case WORK = 'work';
    case BREAK = 'break';
    case SICK = 'sick';
    case VACATION = 'vacation';
    case HOLIDAY = 'holiday';
}
