<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'valid_from',
    ];

    protected $casts = [
        'sunday' => 'float',
        'monday' => 'float',
        'tuesday' => 'float',
        'wednesday' => 'float',
        'thursday' => 'float',
        'friday' => 'float',
        'saturday' => 'float',
        'valid_from' => 'date',
    ];

    protected function getIsCurrentAttribute(): bool
    {
        $firstValid = self::where('valid_from', '<=', now())->orderByDesc('valid_from')->first();

        return $this->id === $firstValid?->id;
    }
}
