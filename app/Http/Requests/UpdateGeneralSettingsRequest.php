<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Native\Desktop\Enums\SystemThemesEnum;

class UpdateGeneralSettingsRequest extends FormRequest
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
            'openAtLogin' => ['required', 'boolean'],
            'theme' => ['required', Rule::enum(SystemThemesEnum::class)],
            'showTimerOnUnlock' => ['required', 'boolean'],
            'holidayRegion' => ['nullable', 'string', 'max:5', 'min:2'],
            'locale' => ['required', 'string', 'regex:/^[a-z]{2}_[A-Z]{2}$/'],
            'appActivityTracking' => ['required', 'boolean'],
            'timezone' => ['required', 'string', 'timezone'],
            'default_overview' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
        ];
    }
}
