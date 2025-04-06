<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Native\Laravel\Enums\SystemThemesEnum;

class StoreSettingsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'openAtLogin' => ['required', 'boolean'],
            'theme' => ['required', Rule::enum(SystemThemesEnum::class)],
            'showTimerOnUnlock' => ['required', 'boolean'],
            'holidayRegion' => ['nullable', 'string', 'max:5', 'min:2'],
            'stopBreakAutomatic' => ['nullable', 'string'],
            'stopBreakAutomaticActivationTime' => ['nullable', 'integer', 'min:13', 'max:23'],
            'stopWorkTimeReset' => ['nullable', 'string'],
            'stopBreakTimeReset' => ['nullable', 'string'],
            'locale' => ['required', 'string', 'regex:/^[a-z]{2}-[A-Z]{2}$/'],
            'appActivityTracking' => ['required', 'boolean'],
        ];
    }
}
