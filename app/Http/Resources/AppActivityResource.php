<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\ActivityHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ActivityHistory
 */
class AppActivityResource extends JsonResource
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
            'app_identifier' => $this->app_identifier,
            'app_name' => $this->app_name,
            'app_icon' => route('app-icon.show', ['appIconName' => $this->app_icon]),
            'app_category' => $this->app_category,
            'started_at' => DateHelper::toResourceArray($this->started_at, true),
            'ended_at' => DateHelper::toResourceArray($this->ended_at),
            'duration' => $this->duration,
        ];
    }
}
