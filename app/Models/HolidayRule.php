<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayRule extends Model
{
    #[\Override]
    protected $primaryKey = 'date';

    #[\Override]
    public $incrementing = false;

    #[\Override]
    protected $keyType = 'date';

    #[\Override]
    protected $fillable = [
        'date',
        'is_holiday',
    ];

    #[\Override]
    public $timestamps = false;

    #[\Override]
    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
    ];
}
