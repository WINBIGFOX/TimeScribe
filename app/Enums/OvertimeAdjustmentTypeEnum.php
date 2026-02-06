<?php

declare(strict_types=1);

namespace App\Enums;

enum OvertimeAdjustmentTypeEnum: string
{
    use BaseEnumTrait;
    case Absolute = 'absolute';
    case Relative = 'relative';

    public function label(): string
    {
        return __('app.'.$this->value);
    }
}
