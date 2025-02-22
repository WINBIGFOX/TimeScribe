<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AbsenceTypeEnum;
use App\Jobs\CalculateWeekBalance;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'type',
        'date',
        'duration',
    ];

    protected $casts = [
        'type' => AbsenceTypeEnum::class,
        'date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function () {
            CalculateWeekBalance::dispatch();
        });

        static::updated(function () {
            CalculateWeekBalance::dispatch();
        });

        static::deleted(function () {
            CalculateWeekBalance::dispatch();
        });

    }
}
