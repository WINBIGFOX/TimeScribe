<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Absence
 */
class AbsenceResource extends JsonResource
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
            'type' => $this->type,
            'date' => DateHelper::toResourceArray($this->date),
            'duration' => $this->duration,
        ];
    }
}
