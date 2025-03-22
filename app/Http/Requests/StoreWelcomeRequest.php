<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWelcomeRequest extends FormRequest
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
            'openAtLogin' => ['required_without:workdays', 'boolean'],
            'workdays' => ['required_without:openAtLogin', 'array'],
            'workdays.monday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.tuesday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.wednesday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.thursday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.friday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.saturday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
            'workdays.sunday' => ['required_with:workdays', 'decimal:0,1', 'between:0,15'],
        ];
    }
}
