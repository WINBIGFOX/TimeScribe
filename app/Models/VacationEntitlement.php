<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacationEntitlement extends Model
{
    protected $fillable = [
        'year',
        'days',
        'carryover',
    ];

    protected $casts = [
        'year' => 'integer',
        'days' => 'float',
        'carryover' => 'float',
    ];
}
