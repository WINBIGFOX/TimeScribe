<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    protected $fillable = [
        'app_name',
        'app_identifier',
        'app_icon',
        'app_category',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('ended_at', '>', Carbon::now()->subSeconds(7));
    }
}
