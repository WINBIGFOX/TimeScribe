<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekBalance extends Model
{
    #[\Override]
    protected $fillable = [
        'start_week_at',
        'end_week_at',
        'balance',
        'start_balance',
        'end_balance',
    ];

    #[\Override]
    protected $casts = [
        'start_week_at' => 'datetime',
        'end_week_at' => 'datetime',
    ];
}
