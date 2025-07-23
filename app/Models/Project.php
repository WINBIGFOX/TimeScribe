<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\TimestampService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'hourly_rate',
        'currency',
    ];

    public function scopeSortedByLatestTimestamp($query): Builder
    {
        return $query
            ->leftJoin('timestamps', 'projects.id', '=', 'timestamps.project_id')
            ->select('projects.*')
            ->selectRaw('MAX(timestamps.started_at) as last_started_at')
            ->groupBy('projects.id')
            ->orderByRaw('last_started_at IS NULL DESC, last_started_at DESC');
    }

    public function timestamps(): HasMany
    {
        return $this->hasMany(Timestamp::class)->orderBy('started_at');
    }

    public function getWorkTimeAttribute(): float
    {
        $timestamps = $this->timestamps()->get();

        if ($timestamps->isEmpty()) {
            return 0.0;
        }

        $firstTimestamp = $timestamps->first();
        $lastTimestamp = $timestamps->last();

        return TimestampService::getWorkTime(
            date: $firstTimestamp->started_at,
            endDate: $lastTimestamp->started_at->endOfDay(),
            project: $this
        );
    }

    public function getBillableAmountAttribute(): float
    {
        return round($this->work_time / 60 / 60 * ($this->hourly_rate ?? 0.0), 2);
    }
}
