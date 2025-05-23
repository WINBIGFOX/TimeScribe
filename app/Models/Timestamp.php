<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TimestampTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timestamp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'started_at',
        'ended_at',
        'last_ping_at',
        'description',
        'source',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'type' => TimestampTypeEnum::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_ping_at' => 'datetime',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
