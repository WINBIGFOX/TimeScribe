<?php

declare(strict_types=1);

namespace App\Enums;

enum AbsenceTypeEnum: string
{
    case VACATION = 'vacation';
    case SICK = 'sick';
}
