<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\Absence;
use App\Services\VacationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Absence
 */
class VacationAbsenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $metrics = VacationService::metricsForAbsence($this->resource);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'date' => DateHelper::toResourceArray($this->date),
            'hours' => round($metrics['hours'], 2),
            'day_equivalent' => round($metrics['days'], 2),
            'status' => $metrics['status'],
            'plan_hours' => $metrics['plan_hours'],
        ];
    }
}
