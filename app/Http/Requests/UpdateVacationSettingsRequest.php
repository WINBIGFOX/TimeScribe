<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVacationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_entitlement_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'prorate_consumption' => ['required', 'boolean'],
            'proration_step' => ['nullable', 'numeric'],
            'auto_carryover' => ['required', 'boolean'],
            'minimum_day_hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
        ];
    }
}
