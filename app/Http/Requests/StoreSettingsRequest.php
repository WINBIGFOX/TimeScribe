<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'startOnLogin' => ['required', 'boolean'],
            'workdays' => ['required', 'array'],
            'workdays.monday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.tuesday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.wednesday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.thursday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.friday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.saturday' => ['required', 'decimal:0,1', 'between:0,15'],
            'workdays.sunday' => ['required', 'decimal:0,1', 'between:0,15'],
        ];
    }
}
