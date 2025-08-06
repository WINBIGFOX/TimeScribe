<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayRule extends Model
{
    protected $primaryKey = 'date';

    public $incrementing = false;

    protected $keyType = 'date';

    protected $fillable = [
        'date',
        'is_holiday',
    ];

    public $timestamps = false;

    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
    ];
}
