<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required_with:hourly_rate', Rule::enum(CurrencyAlpha3::class)],
        ];
    }

    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => __('app.project name'),
            'color' => __('app.color'),
            'description' => __('app.description'),
            'hourly_rate' => __('app.hourly rate'),
            'currency' => __('app.currency'),
        ];
    }
}
