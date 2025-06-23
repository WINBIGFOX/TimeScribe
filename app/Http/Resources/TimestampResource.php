<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\Timestamp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Timestamp */
class TimestampResource extends JsonResource
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
            'started_at' => DateHelper::toResourceArray($this->started_at, true, 'Gi'),
            'ended_at' => DateHelper::toResourceArray($this->ended_at, true, 'Gi') ?? null,
            'description' => $this->description,
            'last_ping_at' => DateHelper::toResourceArray($this->last_ping_at, true, 'Gi') ?? null,
            'source' => $this->source,
            'project' => ProjectResource::make($this->whenLoaded('project')),
        ];
    }
}
