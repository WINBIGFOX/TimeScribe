<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\OvertimeAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OvertimeAdjustment
 */
class OvertimeAdjustmentResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'effective_date' => DateHelper::toResourceArray($this->effective_date),
            'type' => $this->type->value,
            'seconds' => $this->seconds,
            'note' => $this->note,
            'year' => $this->effective_date->weekYear,
            'week' => $this->effective_date->week,
        ];
    }
}
