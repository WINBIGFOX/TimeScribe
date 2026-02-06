<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Models\WeekBalance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WeekBalance
 */
class WeekBalanceResource extends JsonResource
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
            'week_number' => $this->start_week_at->week,
            'year' => $this->start_week_at->weekYear,
            'start_date' => DateHelper::toResourceArray($this->start_week_at),
            'end_date' => DateHelper::toResourceArray($this->end_week_at),
            'balance' => $this->balance,
            'start_balance' => $this->start_balance,
            'end_balance' => $this->end_balance,
        ];
    }
}
