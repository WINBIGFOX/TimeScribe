<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkSchedule
 */
class WorkScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'valid_from' => DateHelper::toResourceArray($this->valid_from),
            'is_current' => $this->whenAppended('is_current'),
            'sunday' => $this->sunday,
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
        ];
    }
}
