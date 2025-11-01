<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStartStopSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stopBreakAutomatic' => ['nullable', 'string'],
            'stopBreakAutomaticActivationTime' => ['nullable', 'integer', 'min:13', 'max:23'],
            'stopWorkTimeReset' => ['nullable', 'string'],
            'stopBreakTimeReset' => ['nullable', 'string'],
        ];
    }
}
