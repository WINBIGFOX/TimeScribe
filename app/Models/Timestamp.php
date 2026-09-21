<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TimestampTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Timestamp extends Model
{
    use SoftDeletes;

    protected $appends = [
        'duration',
    ];

    protected $fillable = [
        'type',
        'started_at',
        'ended_at',
        'last_ping_at',
        'description',
        'source',
        'created_at',
        'updated_at',
        'project_id',
        'paid',
    ];

    protected $casts = [
        'type' => TimestampTypeEnum::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_ping_at' => 'datetime',
        'paid' => 'boolean',
    ];

    #[Scope]
    protected function paid($query): void
    {
        $query->where('paid', true);
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->started_at->diffInSeconds($this->ended_at ?? now())
        );
    }

    protected function appUsage(): Attribute
    {
        return Attribute::make(
            get: function (): Collection {
                $endedAt = $this->ended_at ?? now();

                return ActivityHistory::query()
                    ->select([
                        'app_name',
                        'app_identifier',
                        'app_icon',
                        'app_category',
                        'started_at',
                        'ended_at',
                    ])
                    ->where('started_at', '<', $endedAt)
                    ->where('ended_at', '>', $this->started_at)
                    ->get()
                    ->map(function (ActivityHistory $activity) use ($endedAt): array {
                        $startedAt = $activity->started_at->greaterThan($this->started_at)
                            ? $activity->started_at
                            : $this->started_at;
                        $activityEndedAt = $activity->ended_at->lessThan($endedAt)
                            ? $activity->ended_at
                            : $endedAt;

                        $appUsage = [
                            'app_name' => $activity->app_name,
                            'app_identifier' => $activity->app_identifier,
                            'app_icon' => route('app-icon.show', ['appIconName' => $activity->app_icon]),
                            'duration' => (int) $startedAt->diffInSeconds($activityEndedAt),
                        ];

                        if ($activity->app_category) {
                            $appUsage['app_category'] = $activity->app_category->value;
                        }

                        return $appUsage;
                    })
                    ->groupBy('app_identifier')
                    ->map(function (Collection $activities): array {
                        $firstActivity = $activities->first();

                        return [
                            'app_name' => $firstActivity['app_name'],
                            'app_identifier' => $firstActivity['app_identifier'],
                            'app_icon' => $firstActivity['app_icon'],
                            ...($firstActivity['app_category'] ?? null ? ['app_category' => $firstActivity['app_category']] : []),
                            'duration' => $activities->sum('duration'),
                        ];
                    })
                    ->sortByDesc('duration')
                    ->values();
            },
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }
}
