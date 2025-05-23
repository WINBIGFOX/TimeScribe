<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
    ];

    public function timestamps(): HasMany
    {
        return $this->hasMany(Timestamp::class);
    }
}
