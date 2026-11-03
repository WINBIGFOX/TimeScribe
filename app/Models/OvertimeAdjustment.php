<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OvertimeAdjustmentTypeEnum;
use Illuminate\Database\Eloquent\Model;

class OvertimeAdjustment extends Model
{
    #[\Override]
    protected $fillable = [
        'effective_date',
        'type',
        'seconds',
        'note',
    ];

    #[\Override]
    protected $casts = [
        'effective_date' => 'date:Y-m-d',
        'type' => OvertimeAdjustmentTypeEnum::class,
        'seconds' => 'integer',
    ];
}
