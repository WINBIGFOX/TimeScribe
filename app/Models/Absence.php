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

    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (): void {
            CalculateWeekBalance::dispatch();
        });

        static::updated(function (): void {
            CalculateWeekBalance::dispatch();
        });

        static::deleted(function (): void {
            CalculateWeekBalance::dispatch();
        });

    }
}
