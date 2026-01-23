<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TimestampTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    protected function paid($query): Builder
    {
        return $query->where('paid', true);
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->started_at->diffInSeconds($this->ended_at ?? now())
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }
}
