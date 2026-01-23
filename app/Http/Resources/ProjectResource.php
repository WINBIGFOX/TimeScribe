<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'hourly_rate' => $this->hourly_rate,
            'currency' => $this->currency,
            'timestamps' => TimestampResource::collection($this->whenLoaded('timestampItems')),
            'work_time' => $this->whenAppended('work_time'),
            'billable_amount' => $this->whenAppended('billable_amount'),
            'archived_at' => DateHelper::toResourceArray($this->deleted_at),
        ];
    }
}
